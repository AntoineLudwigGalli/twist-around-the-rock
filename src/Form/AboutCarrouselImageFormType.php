<?php

namespace App\Form;

use App\Entity\CarrouselImages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AboutCarrouselImageFormType extends AbstractType
{
    private array $allowedMimeTypes = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'image',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner le nom de l\'image pour que le site soit accessible pour tous.'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => "Le nom de l\image doit contenir au minimum {{ limit }} caractères.",
                        'maxMessage' => "Le nom de l\image doit contenir au maximum {{ limit }} caractères.",
                    ])
                ],
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'label' => 'Sélectionnez l\'image à ajouter au carrousel',
                'data_class' => null,
                'attr' => [
                    'accept' => implode(", ", $this->allowedMimeTypes),
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'maxSizeMessage' => 'Fichier trop volumineux ({{ size }} {{ suffix }}). La taille maximum autorisée est de {{ limit }} {{ suffix }}.',
                        'mimeTypes' => $this->allowedMimeTypes,
                        'mimeTypesMessage' => "Ce type de fichier {{ type }} n'est pas autorisé. Les types autorisés sont {{ types }}."
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-primary col-12'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarrouselImages::class,
            'required' => false
        ]);
    }
}
