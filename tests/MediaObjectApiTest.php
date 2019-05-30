<?php

namespace App\Tests;

use App\Entity\MediaObject;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaObjectApiTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    /** @var Client */
    protected $client;

    // /** @var integer */
    // protected $idMediaObject = 3;

    /**
     * Retrieves the mediaObject list.
     */
    public function testRetrieveTheMediaObjectList(): void
    {
        $response = $this->request('GET', '/api/media_objects');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(0, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(0, $json['hydra:member']);
    }

    /**
     * Throws errors when data are invalid.
     */
    public function testThrowErrorsWhenDataAreInvalid(): void
    {
        $response = $this->requestFile('POST', '/api/media_objects');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertContains('"file" is required', $json);
    }

    /**
     * Creates a mediaObject.
     */
    public function testCreateAMediaObject(): void
    {
        $filesystem = new Filesystem();
        $name = 'symfony.png';
        $copyname = 'symfonysend.png';
        $dirpath = $this->getProjectDir().'/public/images/';
        if ($filesystem->exists($dirpath.$name)) {
            $filesystem->copy($dirpath.$name, $dirpath.$copyname, true);
        }
        $file = new UploadedFile(
            $dirpath.$copyname,
            'symfony.png',
            'image/png',
            null
        );

        $dataFile = [
            'file' => $file,
        ];

        $response = $this->requestFile('POST', '/api/media_objects', $dataFile);
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('contentUrl', $json);
        $this->assertEquals('/uploads/media/symfony.png', $json['contentUrl']);
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

    /**
     * @param array $dataFile
     */
    protected function requestFile(string $method, string $uri, $dataFile = []): Response
    {
        $server = ['CONTENT_TYPE' => 'multipart/form-data', 'HTTP_ACCEPT' => 'application/ld+json'];

        $this->client->request($method, $uri, [], $dataFile, $server);

        return $this->client->getResponse();
    }

    protected function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }
}
