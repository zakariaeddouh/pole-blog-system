<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // --- 1) ADMIN ---
        $admin = new User();
        $admin->setEmail('admin@blog.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('Master');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // --- 2) USERS ---
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email());
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        // --- 3) CATEGORIES ---
        $categories = [];
        $categoryNames = ['Symfony', 'PHP', 'JavaScript', 'DevOps', 'Tutoriels'];

        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription("Articles concernant $name.");
            $manager->persist($category);
            $categories[] = $category;
        }

        // --- 4) POSTS + COMMENTS ---
        foreach ($categories as $category) {

            for ($i = 0; $i < random_int(3, 7); $i++) {
                $post = new Post();
                $post->setTitle($faker->sentence(6));
                $post->setContent($faker->paragraphs(4, true));
                $post->setPicture($faker->imageUrl(800, 400, 'tech'));
                $post->setPublishedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()));
                $post->setCategoty($category);
                $post->setAuthor($faker->randomElement($users));

                $manager->persist($post);

                // COMMENTS
                for ($c = 0; $c < random_int(0, 5); $c++) {
                    $comment = new Comment();
                    $comment->setContent($faker->paragraph(2));
                    $post->setPublishedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()));
                    $comment->setStatus($faker->randomElement(['approved', 'pending', 'rejected']));
                    $comment->setAuthor($faker->randomElement($users));
                    $comment->setPost($post);

                    $manager->persist($comment);
                }
            }
        }

        $manager->flush();
    }
}
