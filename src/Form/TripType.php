<?php

namespace App\Form;


use App\Entity\City;
use App\Entity\Trip;
use App\Repository\CityRepository;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start')
            ->add('end')
            ->add('maxSeats')
            ->add('seatPrice')
            ->add('fromC', EntityType::class, [
                'class' => City::class,
                'query_builder' => function (CityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => 'name',
            ])
            ->add('toC', EntityType::class, [
                'class' => City::class,
                'query_builder' => function (CityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
            'translation_domain' => 'forms',
        ]);
    }
}
