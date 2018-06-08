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

namespace Pd\MailerBundle\Form;

use Pd\MailerBundle\Entity\MailTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Mail Template Form.
 *
 * @author  Ramazan Apaydın <iletisim@ramazanapaydin.com>
 */
class TemplateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('templateId', TextType::class, [
                'label' => 'mail_templateid',
                'label_attr' => ['info' => 'mail_templateid_info'],
            ])
            ->add('language', LanguageType::class, [
                'label' => 'mail_language',
                'required' => true,
                'preferred_choices' => [
                    'tr', 'en', 'de', 'fr', 'ru', 'ar', 'es', 'sv', 'no', 'ga',
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'mail_subject',
            ])
            ->add('template', TextareaType::class, [
                'label' => 'mail_template_content',
                'label_attr' => ['info' => 'mail_template_content_info'],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('fromName', TextType::class, [
                'label' => 'mail_from_name',
                'required' => false,
            ])
            ->add('fromEmail', EmailType::class, [
                'label' => 'mail_from_email',
                'required' => false,
            ])
            ->add('status', CheckboxType::class, [
                'label' => 'enable',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ]);
    }

    /**
     * Form Default Options.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => MailTemplate::class,
            ]);
    }
}