<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProduitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            'label' => 'Titre :',
        ])
        ->add('description', TextType::class, [
            'label' => 'Description :',
        ])
            ->add('caracter', TextType::class, [
                'label' => 'Caractéristique',
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'multiple' => false,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez telechargé une image',
                    ]),
                    new File([
                        'maxSize' => '1500k',
                        'maxSizeMessage' => 'La taille de l\'image ne peut pas dépasser {{ limit }}.',
                    ]),
                ],
            ])
            ->add('price', TextType::class, [
                'label' => 'Prix',
            ])
            ->add('stock', TextType::class, [
                'label' => 'Quantité',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
