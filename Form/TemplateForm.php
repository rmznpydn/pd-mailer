<?php

/**
 * This file is part of the pd-admin pd-mailer package.
 *
 * @package     pd-mailer
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-mailer
 */

namespace Pd\MailerBundle\Form;

use Pd\MailerBundle\Entity\MailTemplate;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Mail Template Form.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
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
            ->add('language', ChoiceType::class, [
                'label' => 'mail_language',
                'choices' => $this->getLanguageList($options['parameters']),
                'choice_translation_domain' => false,
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
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['data_class' => MailTemplate::class])
            ->setRequired('parameters');
    }

    /**
     * Return Active Language List.
     *
     * @return array|bool
     */
    public function getLanguageList(ParameterBagInterface $bag)
    {
        return array_flip(array_intersect_key(Languages::getNames(), array_flip($bag->get('pd_mailer.active_language'))));
    }
}
