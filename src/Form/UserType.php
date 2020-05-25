<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', null, [
                'attr' => ['autofocus' => true]
            ])
            ->add('surname')
            ->add('mail', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('locale', ChoiceType::class, [
                'choices' => $this->getLocales()
            ])
            ->add('theme', ChoiceType::class, [
                'choices' => $this->getThemes()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms',
        ]);
    }


    private function getThemes(){
        $choices = User::THEMES;
        $themes = [];
        foreach($choices as $k => $v){
            $themes[$v] = $v;
        }
        return $themes;
    }

    private function getLocales(){
        $choices = User::LOCALES;
        $locales = [];
        foreach($choices as $k => $v){
            $locales[$v] = $v;
        }
        return $locales;
    }
}
