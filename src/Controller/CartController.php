<?php

namespace App\Controller;


use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(private RequestStack $requestStack)
    {
    }
    #[Route('/', name: 'app_cart_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $cart= $this->getCart();
        $cartProducts = [];
        foreach ($cart as $productId =>$quantity){
            $product = $productRepository->find($productId);
            $cartProducts[$productId]=[
                "product"=>$product,
                "quantity"=>$quantity
            ];
        }
        return $this->render('cart/index.html.twig', [
            'cart' => $cartProducts
        ]);
    }
    #[Route('/increase/{id}', name: 'app_cart_increase')]
    public function increaseQuantity(int $id): Response
    {
        $cart = $this->getCart();

        $cart[$id] = isset($cart[$id]) ? $cart[$id] + 1 : 1;

        $this->setCart($cart);

        return $this->redirectToRoute('app_cart_index');
    }
    #[Route('/decrease/{id}', name: 'app_cart_decrease')]
    public function decreaseQuantity(int $id): Response
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            $cart[$id] -= 1;
            if ($cart[$id] === 0) {
                unset($cart[$id]);
            }
        }

        $this->setCart($cart);
        return $this->redirectToRoute('app_cart_index');
    }




    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id): Response
    {
        $cart = $this->getCart();

        unset($cart[$id]);

        $this->setCart($cart);

        return $this->redirectToRoute('app_cart_index');
    }


    /**
     * method to flush the card
     * Return redirect to the cart
     */

    #[Route('/flush', name: 'app_cart_flush')]
    public function flush(): Response
    {
        $this->setCart([]);

        return $this->redirectToRoute('app_cart_index');
    }

    private function getCart(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        return $cart;
    }

    private function setCart(array $cart): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->set('cart', $cart);
    }
}
