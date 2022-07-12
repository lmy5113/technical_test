<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractAppWebTestCaseTest extends WebTestCase
{
    protected KernelBrowser|MockObject $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient([], ['HTTP_HOST' => '192.168.99.10']);
        // $this->client = self::createClient([], []);
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->em->getConnection()->setAutoCommit(true);
    }
}
