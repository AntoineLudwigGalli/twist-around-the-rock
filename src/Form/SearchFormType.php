<?php

namespace App\Form;

use App\Data\SearchData;
use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Stone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' =>[
                    'placeholder' => 'Trouvez votre bonheur'
                ]
            ])

            ->add('categories', EntityType::class, [
                'label' => 'Catégorie de produits ',
                'required' => false,
                'class' => Category::class,
                'expanded' => true,
                'multiple' => true,
            ])

            ->add('colors', EntityType::class, [
                'label' => 'Couleur du produit',
                'required' => false,
                'class' => Color::class,
                'expanded' => true,
                'multiple' => true,
            ])

            ->add('stones', EntityType::class, [
                'label' => 'Pierre présente',
                'required' => false,
                'class' => Stone::class,
                'expanded' => true,
                'multiple' => true,
            ])

            ->add('min', NumberType::class, [
                'label' => false,
                'required' => false,
                'attr' =>[
                    'placeholder' => 'Prix minimum'
                ]
            ])

            ->add('max', NumberType::class, [
                'label' => false,
                'required' => false,
                'attr' =>[
                    'placeholder' => 'Prix maximum'
                ]
            ])

            ->add('available', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
