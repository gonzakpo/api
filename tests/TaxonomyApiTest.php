<?php

namespace App\Tests;

use App\Entity\Taxonomy;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaxonomyApiTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    /** @var Client */
    protected $client;

    /** @var int */
    protected $idTaxonomy = 3;

    /**
     * Retrieves the taxonomy list.
     */
    public function testRetrieveTheTaxonomyList(): void
    {
        $response = $this->request('GET', '/api/taxonomies');
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
        ];

        $response = $this->request('POST', '/api/taxonomies', $data);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('violations', $json);
        $this->assertCount(1, $json['violations']);

        $this->assertArrayHasKey('propertyPath', $json['violations'][0]);
        $this->assertEquals('name', $json['violations'][0]['propertyPath']);
    }

    /**
     * Creates a taxonomy.
     */
    public function testCreateATaxonomy(): void
    {
        $data = [
            'name' => 'Manu Ginobili',
        ];

        $response = $this->request('POST', '/api/taxonomies', $data);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('name', $json);
        $this->assertEquals('Manu Ginobili', $json['name']);
    }

    /**
     * Updates a taxonomy.
     */
    public function testUpdateATaxonomy(): void
    {
        $data = [
            'name' => 'Pepe Sanchez',
        ];

        $response = $this->request('PUT', $this->findOneIriBy(Taxonomy::class, ['id' => $this->idTaxonomy]), $data);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('name', $json);
        $this->assertEquals('Pepe Sanchez', $json['name']);
    }

    /**
     * Deletes a taxonomy.
     */
    public function testDeleteATaxonomy(): void
    {
        $response = $this->request('DELETE', $this->findOneIriBy(Taxonomy::class, ['id' => $this->idTaxonomy]));

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
            if ('content-type' === strtolower($key)) {
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
