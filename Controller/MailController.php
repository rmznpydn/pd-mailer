<?php

/**
 * This file is part of the pdAdmin pdMailer package.
 *
 * @package     pdMailer
 *
 * @author      Ramazan APAYDIN <iletisim@ramazanapaydin.com>
 * @copyright   Copyright (c) 2018 Ramazan APAYDIN
 * @license     LICENSE
 *
 * @link        https://github.com/rmznpydn/pd-mailer
 */

namespace Pd\MailerBundle\Controller;

use Pd\MailerBundle\Entity\MailLog;
use Pd\MailerBundle\Entity\MailTemplate;
use Pd\MailerBundle\Form\TemplateForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mail Controller.
 *
 * routing : /admin/tools/mail/*
 *
 * @author  Ramazan Apaydın <iletisim@ramazanapaydin.com>
 */
class MailController extends Controller
{
    /**
     * List Mail Templates.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @IsGranted("ADMIN_MAIL_TEMPLATELIST")
     */
    public function listAction(Request $request)
    {
        // Get Query
        $query = $this->getDoctrine()
            ->getRepository(MailTemplate::class)
            ->createQueryBuilder('m');

        // Get Result
        $pagination = $this->get('knp_paginator');
        $pagination = $pagination->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('pd_mailer.list_count'))
        );

        // Set Back URL
        $this->get('session')->set('backUrl', $request->getRequestUri());

        // Render
        return $this->render('@PdMailer/list.html.twig', [
            'templates' => $pagination,
        ]);
    }

    /**
     * Add Templates.
     *
     * @param Request $request
     * @param MailLog $mailLog
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @IsGranted("ADMIN_MAIL_TEMPLATEADD")
     */
    public function addTemplateAction(Request $request, MailLog $mailLog)
    {
        // Create Mail Template
        $template = new MailTemplate();
        $template->setTemplateId($mailLog->getTemplateId());
        $template->setSubject($mailLog->getSubject());

        // Create Form
        $form = $this->createForm(TemplateForm::class, $template);

        // Handle Request
        $form->handleRequest($request);

        // Submit & Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Add object
            $template->setTemplateData($mailLog->getBody());

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush();

            // Message
            $this->addFlash('success', 'changes_saved');

            // Return Edit Page
            $this->redirectToRoute('admin_mail_template_edit', ['id' => $template->getId()]);
        }

        return $this->render('@PdMailer/template.html.twig', [
            'form' => $form->createView(),
            'objects' => @unserialize($mailLog->getBody()),
            'title' => 'mail_manager_template_add',
            'description' => 'mail_manager_template_add_desc',
            'defaultTemplate' => $this->get('router')->generate('admin_mail_default_template', ['templateId' => $form->get('templateId')->getData()]),
        ]);
    }

    /**
     * Edit Templates.
     *
     * @param Request      $request
     * @param MailTemplate $mailTemplate
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @IsGranted("ADMIN_MAIL_TEMPLATEEDIT")
     */
    public function editTemplateAction(Request $request, MailTemplate $mailTemplate)
    {
        // Create Form
        $form = $this->createForm(TemplateForm::class, $mailTemplate);

        // Handle Request
        $form->handleRequest($request);

        // Submit & Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($mailTemplate);
            $em->flush();

            // Message
            $this->addFlash('success', 'changes_saved');
        }

        return $this->render('@PdMailer/template.html.twig', [
            'form' => $form->createView(),
            'objects' => @unserialize($mailTemplate->getTemplateData()),
            'title' => 'mail_manager_template_edit',
            'description' => 'mail_manager_template_edit_desc',
            'defaultTemplate' => $this->get('router')->generate('admin_mail_default_template', ['templateId' => $form->get('templateId')->getData()]),
        ]);
    }

    /**
     * Get Default Template.
     *
     * @param $templateId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @IsGranted("ADMIN_MAIL_TEMPLATEADD")
     */
    public function defaultTemplate($templateId)
    {
        // Load Email Template Resource
        $template = $this->getParameter('kernel.root_dir').'/Admin/Resources/emails/';

        if (file_exists($template."{$templateId}.html")) {
            $template = file_get_contents($template."{$templateId}.html");
        } else {
            $template = '';
        }

        return $this->json($template);
    }

    /**
     * Delete Templates.
     *
     * @param Request      $request
     * @param MailTemplate $mailTemplate
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @IsGranted("ADMIN_MAIL_TEMPLATEDELETE")
     */
    public function deleteTemplateAction(Request $request, MailTemplate $mailTemplate)
    {
        // Not Found
        if (null === $mailTemplate) {
            $this->addFlash('error', 'sorry_not_existing');

            return $this->redirectToRoute('admin_mail_list');
        }

        // Remove Template
        $em = $this->getDoctrine()->getManager();
        $em->remove($mailTemplate);
        $em->flush();

        // Redirect Back
        return $this->redirect(($r = $request->headers->get('referer')) ? $r : $this->generateUrl('admin_mail_list'));
    }

    /**
     * Active/Deactive Templates.
     *
     * @param Request      $request
     * @param MailTemplate $mailTemplate
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @IsGranted("ADMIN_MAIL_TEMPLATEACTIVE")
     */
    public function activeTemplateAction(Request $request, MailTemplate $mailTemplate)
    {
        // Set Status
        $mailTemplate->setStatus(!$mailTemplate->getStatus());

        // Save
        $em = $this->getDoctrine()->getManager();
        $em->persist($mailTemplate);
        $em->flush();

        // Message
        $this->addFlash('success', 'changes_saved');

        // Redirect Back
        return $this->redirect(($r = $request->headers->get('referer')) ? $r : $this->generateUrl('admin_mail_list'));
    }

    /**
     * View Mail Logs.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @IsGranted("ADMIN_MAIL_LOGGER")
     */
    public function loggerAction(Request $request)
    {
        // Get Logs
        $query = $this->getDoctrine()
            ->getRepository(MailLog::class)
            ->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC')
            ->getQuery();

        // Get Result
        $pagination = $this->get('knp_paginator');
        $mailLog = $pagination->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('pd_mailer.list_count'))
        );

        return $this->render('@PdMailer/logger.html.twig', [
            'maillogs' => $mailLog,
        ]);
    }

    /**
     * View Log.
     *
     * @param MailLog $log
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @IsGranted("ADMIN_MAIL_VIEWLOG")
     */
    public function viewLogAction(MailLog $log)
    {
        // Get Log Manager
        $trans = $this->get('translator');

        $data = [
            $trans->trans('mail_templateid') => $log->getTemplateId(),
            $trans->trans('mail_mid') => $log->getMailId(),
            $trans->trans('mail_to') => implode(PHP_EOL, $this->implodeKeyValue($log->getTo(), ' -> ')),
            $trans->trans('mail_from') => implode(PHP_EOL, $this->implodeKeyValue($log->getFrom(), ' -> ')),
            $trans->trans('mail_subject') => $log->getSubject(),
            $trans->trans('mail_language') => $log->getLanguage(),
            $trans->trans('mail_content_type') => $log->getContentType(),
            $trans->trans('date') => date('Y-m-d H:i:s', $log->getDate()->getTimestamp()),
            $trans->trans('mail_reply_to') => $log->getReplyTo(),
            $trans->trans('mail_header') => str_replace(PHP_EOL, '<br/>', htmlspecialchars($log->getHeader())),
            $trans->trans('mail_status') => $log->getStatus(),
            $trans->trans('mail_exception') => str_replace(PHP_EOL, '<br/>', htmlspecialchars($log->getException())),
        ];

        // JSON Response
        return $this->json($data);
    }

    /**
     * Delete Logs.
     *
     * @param Request $request
     * @param $mailLog
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @IsGranted("ADMIN_MAIL_LOGDELETE")
     */
    public function deleteLogAction(Request $request, $mailLog)
    {
        // Not Found
        if (null === $mailLog && !$request->request->has('id')) {
            $this->addFlash('error', 'sorry_not_existing');

            return $this->redirectToRoute('admin_mail_logger');
        }

        // Convert Array
        $mailLog = $request->request->has('id') ? $request->request->get('id') : [$mailLog];

        // Remove Mail Log
        if ($mailLog) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->createQueryBuilder('')
                ->delete(MailLog::class, 'log')
                ->add('where', $em->getExpressionBuilder()->in('log.id', ':logId'))
                ->setParameter(':logId', $mailLog)
                ->getQuery()
                ->execute();
        }

        // Redirect Back
        return $this->redirect(($r = $request->headers->get('referer')) ? $r : $this->generateUrl('admin_mail_logger'));
    }

    /**
     * Array Key => Value Implode.
     *
     * @param array  $array
     * @param string $glue
     *
     * @return array
     */
    private function implodeKeyValue(array $array, $glue = ' - ')
    {
        $imploded = [];

        // Imlode Key => Value
        foreach ($array as $key => $value) {
            $imploded[] = "{$key}{$glue}{$value}";
        }

        return $imploded;
    }
}