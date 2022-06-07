<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Order;
use App\Entity\Product;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrderController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

      /**
       * @Route("/api/cart/{id}", name="add_product_cart", methods={"POST"})
       */
      public function addToCart(int $id, EntityManagerInterface $em) : Response
      {
          $product = $this->doctrine->getRepository(Product::class)->find($id);

          if (!$product) {
              return $this->json(['error' => "No product found for id $id."], Response::HTTP_NOT_FOUND);
          }

          $user = $this->getUser();
          $user->addCart($product);
          $em->flush();
          $name = $product->getName();
          return $this->json(['message' => "The product $name has been added in the cart!"], Response::HTTP_OK);
      }

     /**
      * @Route("/api/cart/{id}", name="delete_product_cart", methods={"DELETE"})
      */
     public function removeFromCart(int $id, EntityManagerInterface $em) : Response
     {
         $product = $this->doctrine->getRepository(Product::class)->find($id);

         if (!$product) {
             return $this->json(['error' => "No product found for id $id."], Response::HTTP_NOT_FOUND);
         }

         $user = $this->getUser();
         $user->removeCart($product);
         $em->flush();
         $name = $product->getName();
         return $this->json(['message' => "The product $name has been removed from the cart!"], Response::HTTP_OK);
     }

     /**
      * @Route("/api/cart/", name="list_cart"), methods={"GET"}
      */
     public function listCart() : Response
     {
         $user = $this->getUser();
         $cart = $user->getCart();

         if ($user->getCart()->isEmpty()) {
             return $this->json(["message" => "Your cart is empty."], Response::HTTP_OK);
         }

         $data = [];

         foreach ($cart as $product) {
             $data[] = [
                 "id" => $product->getId(),
                 "name" => $product->getName(),
                 "description" => $product->getDescription(),
                 "photo" => $product->getPhoto(),
                 "price" => $product->getPrice(),
             ];
         }

         return $this->json($data, Response::HTTP_OK);
     }

     /**
      * @Route("/api/cart/validate", name="validate_cart"), methods={"POST"}
      */
     public function validateCart(EntityManagerInterface $em): Response
     {
         $user = $this->getUser();
é
         $order = new Order();
         $products = $user->getCart();

         $order->setCreationDate(new \DateTime());
         $order->setUserId($user);

         $price = 0.0;

         foreach ($products as $product) {
             $price += $product->getPrice();
         }

         $order->setTotalPrice($price);

         $em->persist($order);
         $em->fetch();

         return $this->json(["message" => "Order created!"], Response::HTTP_OK);
     }

    /**
    * @Route("/api/order/{id}", name="order_id", methods={"GET"})
    */
    public function find(int $id): Response {
        $order = $this->doctrine->getRepository(Order::class)->find($id);

        if (!$order) {
            return $this->json(['error' => "No order found for id $id."], Response::HTTP_NOT_FOUND);
        }

        $products = [];

        foreach ($order->getProducts() as $product) {
            $products[] = [
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'photo' => $product->getPhoto(),
            ];
        }

        $data = [
            'id' => $order->getId(),
            'totalPrice' => $order->getTotalPrice(),
            'creationDate' => $order->getCreationDate(),
            'products' => $products,
        ];

        return $this->json($data, Response::HTTP_OK);
    }

    /**
    * @Route("/api/orders/", name="user_orders", methods={"GET"})
    */
    public function index(): Response {
        $user = $this->getUser();
        $orders = $this->doctrine->getRepository(Order::class)->findBy(["user_id" => $user]);

        $data = [];

        foreach ($orders as $order) {
            $products = [];
            foreach ($order->getProducts() as $product) {
                $products[] = [
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'photo' => $product->getPhoto(),
                ];
            }

            $data[] = [
                 'id' => $order->getId(),
                 'totalPrice' => $order->getTotalPrice(),
                 'creationDate' => $order->getCreationDate(),
                 "products" => $products,
            ];
         }
        return $this->json($data, Response::HTTP_OK);
    }
}
