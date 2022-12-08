<?php

namespace App\Form;

use App\Entity\Product;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductFormType extends AbstractType
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
                'label' => 'Nom du produit',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un nom pour le produit'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => "Le nom doit contenir au minimum {{ limit }} caractères.",
                        'maxMessage' => "Le nom doit contenir au maximum {{ limit }} caractères.",
                    ])
                ],
            ])
            ->add('coverImage', FileType::class, [
                'mapped' => false,
                'label' => 'Sélectionnez l\'image à ajouter en couverture du produit',
                'data_class' => null,
                'required' => false,
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
            ->add('content', CKEditorType::class, [
                'label' => 'Description du produit',
                'attr' => [
                    'class' =>'d-none'],
                'purify_html' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un contenu'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50000,
                        'minMessage' => "Le contenu doit contenir au minimum {{ limit }} caractères.",
                        'maxMessage' => "Le contenu doit contenir au maximum {{ limit }} caractères.",
                    ])
                ],
            ])
            ->add('illustrationImageRight', FileType::class, [
                'mapped' => false,
                'label' => 'Sélectionnez l\'image à ajouter dans l\'article à droite',
                'data_class' => null,
                'required' => false,
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
            ->add('illustrationImageLeft', FileType::class, [
                'mapped' => false,
                'label' => 'Sélectionnez l\'image à ajouter dans l\'article à gauche',
                'data_class' => null,
                'required' => false,
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
            ->add('available', ChoiceType::class, [
                'label' => 'Le produit est-il disponible ?',
                'expanded' => true,
                'multiple' => false, //expanded et multiple permettent d'avoir des boutons radio plutôt qu'un menu
                // déroulant
                'choices' => [
                    'Oui' => "1",
                    'Non' => "0",
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'indiquer la disponibilité du produit.'
                    ])
                ]
            ])
            ->add('color', TextType::class, [
                'label' => 'Couleur du produit',
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => "Le nom doit contenir au minimum {{ limit }} caractères.",
                        'maxMessage' => "Le nom doit contenir au maximum {{ limit }} caractères.",
                    ])
                ],
            ])
            ->add('stone' , TextType::class, [
                'label' => 'Pierre utilisée',
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => "Le nom de la pierre doit contenir au minimum {{ limit }} caractères.",
                        'maxMessage' => "Le nom de la pierre doit contenir au maximum {{ limit }} caractères.",
                    ])
                ],
            ])
            ->add('type' , TextType::class, [
                'label' => 'Type de bijou',
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => "Le type de bijou doit contenir au minimum {{ limit }} caractères.",
                        'maxMessage' => "Le type de bijou doit contenir au maximum {{ limit }} caractères.",
                    ])
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix indicatif en euros'

            ])
            ->add('creationDate', DateType::class, [
                'label' => 'Sélectionnez la date de création du produit',
                'model_timezone' => 'Europe/Paris', //Date au format FR
                'widget' => 'single_text', //Date au format input date html
                'constraints' => [
                    new NotBlank([ // Erreur si le champ n'est pas rempli
                        'message' => 'Merci de saisir une date valide pour la prochaine émission'
                    ]),
                ]
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-primary w-100 mt-3',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
