<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    private SerializerService $serializerService;
    private EntityManagerInterface $manager;
    private CustomerRepository $customerRepository;

    public function __construct(serializerService $serializer, EntityManagerInterface $entityManager, CustomerRepository $repository)
    {
        $this->serializerService = $serializer;
        $this->manager = $entityManager;
        $this->customerRepository = $repository;

    }

    #[Route('/customer', name: 'get_all_customer', methods: ['GET'])]
    public function getAllCustomers(CustomerRepository $customerRepository): JsonResponse
    {
        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($customerRepository->findAll(), 'json'));
    }

    #[Route('/customer/{id}', name: 'get_customer', methods: ['GET'])]
    public function getCustomerById(CustomerRepository $customerRepository, $id): JsonResponse
    {

        $customer = $customerRepository->findOneBy(['id' => $id]);
        // Find customer's id

        // If customer's id exist
        if (!empty($customer)) {
            // Return its elements
            return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($customerRepository->findOneBy(['id' => $id]), 'json'), Response::HTTP_OK);
        } else {
            // Returns bad request status because customer not found
            return new JsonResponse(['status' => "Can't find this customer"], Response::HTTP_BAD_REQUEST);
        }

    }

    #[Route('/customer', name: 'create_customer', methods: ['POST'])]
    public function createCustomer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['email'];
        $phoneNumber = $data['phoneNumber'];

        if (!empty($firstName) && !empty($lastName) && !empty($email) && !empty($phoneNumber)) {
            $customer = new Customer();

            $customer
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setEmail($email)
                ->setPhoneNumber($phoneNumber);

            $this->manager->persist($customer);
            $this->manager->flush();

            return new JsonResponse(['status' => 'Customer created'], Response::HTTP_CREATED);
        } else {
            return new JsonResponse(['status' => "An error occured"], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/customer/{id}', name: 'update_customer', methods: ['PUT'])]
    public function updateCustomer($id, Request $request): JsonResponse
    {
        $customer = $this->customerRepository->findOneBy(['id' => $id]);
        $data = json_decode($request->getContent(), true);


        if (!empty($customer)) {

            empty($data['firstName']) ?: $customer->setFirstName($data['firstName']);
            empty($data['lastName']) ?: $customer->setLastName($data['lastName']);
            empty($data['email']) ?: $customer->setEmail($data['email']);
            empty($data['phoneNumber']) ?: $customer->setPhoneNumber($data['phoneNumber']);

            $updatedCostumer = $this->customerRepository->update($customer);

            return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($updatedCostumer, 'json'), Response::HTTP_OK);
        } else {
            return new JsonResponse(['status' => "Can't find this customer"], Response::HTTP_BAD_REQUEST);
        }

    }

    #[Route('/customer/{id}', name: 'delete_customer', methods: ['DELETE'])]
    public function deleteCustomer($id): JsonResponse
    {
        $customer = $this->customerRepository->findOneBy(['id' => $id]);

        if (!empty($customer)) {
            $this->customerRepository->remove($customer);
            return new JsonResponse(['status' => 'Customer deleted'], Response::HTTP_NO_CONTENT);
        } else {
            return new JsonResponse(['status' => "Can't find this customer"], Response::HTTP_BAD_REQUEST);
        }
    }

}
