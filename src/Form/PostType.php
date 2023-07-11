<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\Post;
use App\Repository\BoardRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostType extends AbstractType
{
    private $repository;

    public function __construct(BoardRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('postImage', FileType::class, [
                'label' => 'Post image (JPG or PNG file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PNG/JPEG image',
                    ]),
                ],
            ])
            ->add('board', EntityType::class, [
                'class' => Board::class,
                'choices' => $this->getBoardChoices(),
                'choice_label' => 'name',
                'placeholder' => 'Select a board category',
                'label' => 'Board Category',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }

    private function getBoardChoices(): array
    {
        $boards = $this->repository->findAll();

        $choices = [];
        foreach ($boards as $board) {
            $choices[$board->getName()] = $board;
        }

        return $choices;
    }
}
