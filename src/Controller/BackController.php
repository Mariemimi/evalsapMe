<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Category;
use App\Entity\Entreprise;
use App\Entity\Product;
use App\Entity\SubCategory;
use App\Form\AvisType;
use App\Form\CategoryType;
use App\Form\EditProductType;
use App\Form\EntrepriseType;
use App\Form\ProductType;
use App\Form\SubCategoryType;
use App\Repository\CategoryRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{

    #[Route('/ajoutProduit', name: 'ajoutProduit')]
    public function ajoutProduit(Request $request, EntityManagerInterface $manager): Response
    {
        // cette méthode doit nous permettre de créer un nouveau produit. On instancie donc un objet Product de App\Entity que l'on va remplir de toutes ses propriétés
        $product = new Product();

        // ici on instancie un objet Form via la méthode createForm() existante de notre abstractController
        $form = $this->createForm(ProductType::class, $product);
        // cette méthode attend en argument le formulaire à utiliser et l'objet Entité auquel il fait référence, ainsi il va controler la conformité entre les champs de formulaire et les propriétés présentes dans l'entité pour pouvoir remplir l'objet Product par lui-même.

        // grace à la méthode handleRequest de notre objet de formulaire, il charge à présent l'objet Product de données receptionnées du formulaire présentent dans notre objet request (Request étant la classe de symfony qui récupère la majeur partie des données de superGlobale =>$_GET, $_POST ...)
        $form->handleRequest($request);

        // $request->request est la surcouche de $_POST. ->get() permet d'accéder à une entrée de notre tableau de donnée
        //$request->request->get('title');

        //  pour accéder à la surcouche de $_GET on utilise $request->query
        // qui possède les mêmes méthodes que $request->request

        if ($form->isSubmitted() && $form->isValid()) {

            //dd($product);
            // dd($form->get('picture')->getData());
            // on récupère les données de notre input type File du formulaire qui a pour name 'picture'
            $picture = $form->get('picture')->getData();
            // condition d'upload de photo
            if ($picture) {

                $picture_bdd = date('YmdHis') . uniqid() . $picture->getClientOriginalName();

                $picture->move($this->getParameter('upload_directory'), $picture_bdd);
                // move() est une méthode de notre objet File qui permet de déplacer notre fichier temporaire uploadé à un emplacement donné (le 1er paramètre) et de nommé ce fichier (le second paramètre de la méthode)

                $product->setPicture($picture_bdd);
                $manager->persist($product);
                $manager->flush();

                $this->addFlash('success', 'Produit ajouté');
                return $this->redirectToRoute('gestionProduit');


            }


        }


        return $this->render('back/ajoutProduit.html.twig', [
            'form' => $form->createView()

        ]);
    }

    #[Route('/gestionProduit', name: 'gestionProduit')]
    public function gestionProduit(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();


        return $this->render('back/gestionProduit.html.twig', [
            'products' => $products
        ]);
    }


    #[Route('/editProduct/{id}', name: 'editProduct')]
    public function editProduct(Product $product, Request $request, EntityManagerInterface $manager): Response
    {

        $form = $this->createForm(EditProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('editPicture')->getData()) {
                $picture = $form->get('editPicture')->getData();
                $picture_bdd = date('YmdHis') . uniqid() . $picture->getClientOriginalName();

                $picture->move($this->getParameter('upload_directory'), $picture_bdd);
                unlink($this->getParameter('upload_directory') . '/' . $product->getPicture());
                $product->setPicture($picture_bdd);


            }

            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', 'Produit modifié');
            return $this->redirectToRoute('gestionProduit');


        }


        return $this->render('back/editProduct.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }


    #[Route('/deleteProduct/{id}', name: 'deleteProduct')]
    public function deleteProduct(Product $product, EntityManagerInterface $manager): Response
    {
        $manager->remove($product);
        $manager->flush();

        $this->addFlash('success', 'produit supprimé !!!');

        return $this->redirectToRoute('gestionProduit');
    }

    #[Route('/category', name: 'category')]
    #[Route('/editCategory/{id}', name: 'editCategory')]
    public function category(CategoryRepository $repository, EntityManagerInterface $manager, Request $request, $id = null): Response
    {

        $categories = $repository->findAll();

        if ($id) {
            $category = $repository->find($id);
        } else {

            $category = new Category();
        }


        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($category);
            $manager->flush();
            if ($id) {
                $this->addFlash('success', 'Catégorie modifiée');
            } else {
                $this->addFlash('success', 'Catégorie ajoutée');

            }

            return $this->redirectToRoute('category');


        }


        return $this->render('back/category.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories
        ]);
    }


    #[Route('/deleteCategory/{id}', name: 'deleteCategory')]
    public function deleteCategory(CategoryRepository $repository, EntityManagerInterface $manager, $id): Response
    {
        $category = $repository->find($id);

        $manager->remove($category);
        $manager->flush();


        return $this->redirectToRoute('category');
    }


          #[Route('/subCategory', name: 'subCategory')]
              public function subCategory(Request $request, EntityManagerInterface $manager): Response
              {
                  $subCategory = new subCategory();

                  $form = $this->createForm(subCategoryType::class, $subCategory);

                  $form->handleRequest($request);

                  if ($form->isSubmitted() && $form->isValid()):

                          $manager->persist($subCategory);
                          $manager->flush();


                          endif;



                          return $this->render('back/subCategory.html.twig', [
                              'form' => $form->createView()

                  ]);
              }


    #[Route('/gestionSubCategory', name: 'gestionSubCategory')]
    public function gestionSubCategory(SubCategoryRepository $subCategoryRepository): Response
    {
        $subCategories= $subCategoryRepository->findAll();


        return $this->render('back/gestionSubCategory.html.twig', [
            'subCategories' => $subCategories
        ]);
    }

    #[Route('/editSubCategory/{id}', name: 'editSubCategory')]
    public function editSubCategory(SubCategory $subCategory, Request $request, EntityManagerInterface $manager): Response
    {

        $form = $this->createForm(SubCategoryType::class, $subCategory);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()):

            $manager->persist($subCategory);
            $manager->flush();

        endif;

        return $this->render('back/subCategory.html.twig', [
            'form'=>$form->createView()

]);

}

    #[Route('/ajoutEntreprise', name: 'ajoutEntreprise')]
    public function ajoutEntreprise(Request $request, EntityManagerInterface $manager): Response
    {

        $ajoutEntreprise = new Entreprise();


        $form = $this->createForm(EntrepriseType::class, $ajoutEntreprise);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            $ajoutEntreprise->setDate('2022-12-12');


            $manager->persist($ajoutEntreprise);
            $manager->flush();


        endif;

        return $this->render('back/ajoutEntreprise.html.twig', [
            'form' => $form->createView()

        ]);
    }


    #[Route('/gestionEntreprise', name: 'gestionEntreprise')]
    public function gestionEntreprise(EntrepriseRepository $entrepriseRepository): Response
    {
        $entreprises = $entrepriseRepository->findAll();


        return $this->render('back/gestionEntreprise.html.twig', [
            'entreprises' => $entreprises
        ]);
    }



    #[Route('/ajoutAvis/{id}', name: 'ajoutAvis')]
    public function ajoutAvis(ProductRepository $productRepository, Request $request, EntityManagerInterface $manager, $id): Response
    {
        $avis= new  Avis();




        $form = $this->createForm(AvisType::class, $avis);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            $avis->setDate(new \DateTime());
            $avis->setUtilisateur($this->getUser());
            $avis->setProduit($productRepository->find($id));


            $manager->persist($avis);
            $manager->flush();


        endif;

        return $this->render('back/ajoutAvis.html.twig', [
            'form' => $form->createView()

        ]);
    }


    #[Route('/gestionAvis', name: 'gestionAvis')]
    public function gestionAvis(EntrepriseRepository $avisRepository): Response
    {
        $avis = $avisRepository->findAll();


        return $this->render('back/gestionAvis.html.twig', [
            'avis' => $avis
        ]);
    }


}// fermeture de controller
