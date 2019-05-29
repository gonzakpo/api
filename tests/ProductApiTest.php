<?php

namespace App\Tests;

use App\Entity\Product;
use App\Entity\Taxonomy;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductApiTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    /** @var Client */
    protected $client;

    /** @var integer */
    protected $idProduct = 3;
    protected $idTaxonomy = 5;

    /**
     * Retrieves the product list.
     */
    public function testRetrieveTheProductList(): void
    {
        $response = $this->request('GET', '/api/products');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(10, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(10, $json['hydra:member']);
    }

    /**
     * Throws errors when data are invalid.
     */
    public function testThrowErrorsWhenDataAreInvalid(): void
    {
        $data = [
            'name' => '',
            'description' => '',
            'price' => '',
        ];

        $response = $this->request('POST', '/api/products', $data);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('violations', $json);
        $this->assertCount(3, $json['violations']);

        $this->assertArrayHasKey('propertyPath', $json['violations'][0]);
        $this->assertEquals('name', $json['violations'][0]['propertyPath']);

        $this->assertArrayHasKey('propertyPath', $json['violations'][1]);
        $this->assertEquals('description', $json['violations'][1]['propertyPath']);

        $this->assertArrayHasKey('propertyPath', $json['violations'][2]);
        $this->assertEquals('price', $json['violations'][2]['propertyPath']);
    }

    /**
     * Creates a product.
     */
    public function testCreateAProduct(): void
    {
        $taxonomy = $this->findOneIriBy(Taxonomy::class, ['id' => $this->idTaxonomy]);
        $data = [
            'name' => 'Product By Manu Ginobili',
            'description' => 'Product By Manu Ginobili',
            'price' => '200.25',
            'taxonomy' => $taxonomy,
        ];

        $response = $this->request('POST', '/api/products', $data);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('name', $json);
        $this->assertEquals('Product By Manu Ginobili', $json['name']);
    }

    /**
     * Updates a product.
     */
    public function testUpdateAProduct(): void
    {
        $data = [
            'name' => 'Product By Pepe Sanchez',
        ];

        $response = $this->request('PUT', $this->findOneIriBy(Product::class, ['id' => $this->idProduct]), $data);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('name', $json);
        $this->assertEquals('Product By Pepe Sanchez', $json['name']);
    }

    /**
     * Deletes a product.
     */
    public function testDeleteAProduct(): void
    {
        $response = $this->request('DELETE', $this->findOneIriBy(Product::class, ['id' => $this->idProduct]));

        $this->assertEquals(204, $response->getStatusCode());

        $this->assertEmpty($response->getContent());
    }

    /**
     * Retrieves the documentation.
     */
    public function testRetrieveTheDocumentation(): void
    {
        $response = $this->request('GET', '/api', null, ['Accept' => 'text/html']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $this->assertContains('API Platform', $response->getContent());
    }

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @param string|array|null $content
     */
    protected function request(string $method, string $uri, $content = null, array $headers = []): Response
    {
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'content-type') {
                $server['CONTENT_TYPE'] = $value;

                continue;
            }

            $server['HTTP_'.strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (is_array($content) && false !== preg_match('#^application/(?:.+\+)?json$#', $server['CONTENT_TYPE'])) {
            $content = json_encode($content);
        }

        $this->client->request($method, $uri, [], [], $server, $content);

        return $this->client->getResponse();
    }

    protected function findOneIriBy(string $resourceClass, array $criteria): string
    {
        $resource = static::$container->get('doctrine')->getRepository($resourceClass)->findOneBy($criteria);

        return static::$container->get('api_platform.iri_converter')->getIriFromitem($resource);
    }
}