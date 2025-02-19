<?php

namespace App\Controller;
ini_set('memory_limit', '512M'); 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection; // Correct import for DBAL Connection

class SqlController extends AbstractController
{
    #[Route('/export/{tableName}', name: 'export_users', methods: ['GET'])]
    public function exportUsers(string $tableName, Connection $connection): Response
    {
        try {
            // Fetch table data
            $data = $connection->fetchAllAssociative("SELECT * FROM `" . $tableName . "`");

            // Convert fields to camelCase
            $dataWithCamelCase = array_map(function ($row) {
                $camelCaseRow = [];
                foreach ($row as $key => $value) {
                    $camelCaseKey = $this->toCamelCase($key);
                    $camelCaseRow[$camelCaseKey] = $value;
                }
                return $camelCaseRow;
            }, $data);

            // Prepare JSON response
            $responseData = [
                'data' => $dataWithCamelCase,
            ];

            $response = new Response(json_encode($responseData));
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $tableName . '.json"');

            return $response;
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), 500);
        }
    }

    private function toCamelCase(string $snakeCase): string
    {
        return lcfirst(str_replace('_', '', ucwords($snakeCase, '_')));
    }
}
