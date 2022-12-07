<?php

namespace App\Form;

use App\Entity\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticleFormType extends AbstractType
{
    private array $allowedMimeTypes = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un titre'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => "Le titre doit contenir au minimum {{ limit }} caractères.",
                        'maxMessage' => "Le titre doit contenir au maximum {{ limit }} caractères.",
                    ])
                ],
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'label' => 'Sélectionnez l\'image à ajouter en couverture de l\'article',
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
                'label' => 'Contenu',
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
            ->add('save', SubmitType::class, [
                'label' => 'Publier',
                'attr' => [
                    'class' => 'btn btn-primary w-100 mt-3',
                ]
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
