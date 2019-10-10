<?php
/**
 *  This file is a part of SmallScheduler
 *  Copyright 2019 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;

use App\Security\InviteForm;
use App\Security\SignUpForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordForm extends AbstractType
{
    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("token", HiddenType::class, ["required" => true])
            ->add("password", PasswordType::class, ["required" => true, "attr" => ["placeholder" => "Password", "class" => "form-control"]])
            ->add("passwordConfirm", PasswordType::class, ["required" => true, "attr" => ["placeholder" => "Confirm password", "class" => "form-control"]])
            ->add("reset", SubmitType::class, ["attr" => ["class" => "form-control btn btn-success fas fa-check-circle"]])
        ;
    }
    /**
     * Configure options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => ResetPassword::class,
        ]);
    }
}