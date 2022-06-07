<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Product;

class ProductController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}
    
    /**
    * @Route("/api/products", name="products", methods={"GET"})
    */
    public function index(): Response
    {
        $products = $this->doctrine
        ->getRepository(Product::class)
        ->findAll();

        if (!$products) {
            return $this->json(['error' => "Can't parse products."], Response::HTTP_NOT_FOUND);
        }

    $data = [];

    foreach ($products as $product) {
       $data[] = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'photo' => $product->getPhoto(),
       ];
    }
        return $this->json($data, Response::HTTP_OK);
    }

    /**
    * @Route("/api/product/{id}", name="product_id", methods={"GET"})
    */
    public function find(int $id): Response
    {
        $product = $this->doctrine
        ->getRepository(Product::class)
        ->find($id);

        if (!$product) {
            return $this->json(['error' => "No product found for id $id."], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'photo' => $product->getPhoto(),
        ];
        return $this->json($data, Response::HTTP_OK);
    }

    /**
    * @Route("/api/product", name="create_product", methods={"POST"})
    */
    public function create(Request $request, EntityManagerInterface $em): Response {

        $data = json_decode($request->getContent(), true);

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setPhoto($data['photo']);
        $em->persist($product);
        $em->flush();
        return $this->json(["message" => "Product created!"], Response::HTTP_OK);
    }

    /**
    * @Route("/api/product/{id}", name="edit_product", methods={"POST"})
    */
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response {

        $data = json_decode($request->getContent(), true);
        $product = $this->doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json(['error' => "No product found for id $id."], Response::HTTP_NOT_FOUND);
        }

        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setPhoto($data['photo']);
        $em->persist($product);
        $em->flush();

        $data = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'photo' => $product->getPhoto(),
        ];

        return $this->json($data, Response::HTTP_OK);
    }

    /**
    * @Route("/api/product/{id}", name="delete_product", methods={"DELETE"})
    */
    public function delete(int $id, EntityManagerInterface $em): Response {
        $product = $this->doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json(['error' => "No product found for id $id."], Response::HTTP_NOT_FOUND);
        }

        $em->remove($product);
        $em->flush();
        return $this->json(['message' => 'Product deleted!'], Response::HTTP_OK);
    }
}
