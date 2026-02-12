<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['rows' => 12, 'class' => 'form-control'],
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'Priorité d\'affichage',
                'required' => false,
                'help' => 'Plus le chiffre est grand, plus l\'article apparaîtra en premier',
                'attr' => ['min' => 0],
            ])
            ->add('picture', TextType::class, [
                'label' => 'URL de l\'image',
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Categorie',
                'placeholder' => 'Choisir une categorie',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
