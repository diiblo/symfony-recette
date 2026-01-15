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
            $em->flush();
            $this->addFlash('success','Votre recette à bien été modifié');
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('recipe/edit.html.twig',[
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }
}
