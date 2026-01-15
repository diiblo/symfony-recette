<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeController extends AbstractController
{
    #[Route('/recette', name: 'recipe.index')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        //$recipes = $recipeRepository->findWithDurationLowerThan(15);
        $recipes = $recipeRepository->findAll();

        return $this->render('recipe/index.html.twig',[
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recette/{slug}-{id}', name: 'recipe.show', requirements: ['id'=>'\d+', 'slug'=>'[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id, RecipeRepository $recipeRepository): Response
    {
        /*return new JsonResponse([
            'slug' => $slug,
            'id' => $id
        ]);*/
        $recipe = $recipeRepository->find($id);

        if ($recipe->getSlug() != $slug) {
            return $this->redirectToRoute('recipe.show', [
                'slug'=> $recipe->getSlug(),
                'id'=> $recipe->getId(),                
                ]);
        }
        return $this->render('recipe/show.html.twig',[
            'recipe'=> $recipe,
        ]);
     }

    #[Route('/recette/{id}/edit','recipe.edit')]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em): Response{

        //$recipe = $recipeRepository->find($id);
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ){
            //$recipe->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success','Votre recette à bien été modifié');
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('recipe/edit.html.twig',[
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }

    #[Route('/recette/create','recipe.create', methods: ['POST', 'GET'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$recipe->setCreatedAt(new \DateTimeImmutable());
            //$recipe->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success','Rectte Créé avec succès');

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('recipe/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/recette/{id}/delete','recipe.delete', methods: ['DELETE'])]
    public function delete(Request $request, EntityManagerInterface $em, Recipe $recipe): Response
    {
        if($em->getRepository(Recipe::class)->find($recipe->getId())){
            $em->remove($recipe);
            $em->flush();
            $this->addFlash('success','Supprimé avec succès');

            return $this->redirectToRoute('recipe.index');
        }else{
            $this->addFlash('danger','Suppression échoué');
            return $this->redirectToRoute('recipe.index');
        }
    }
}
