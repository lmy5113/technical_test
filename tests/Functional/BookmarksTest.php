<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class BookmarksTest extends AbstractAppWebTestCaseTest
{
    public function createBookmarkIsSuccessfulDataProvider(): array
    {
        return [
            [
                'url' => 'https://www.flickr.com/photos/flickr/51698255936/',
            ],
            [
                'url' => 'https://flic.kr/p/2mLp5mh',
            ],
            [
                'url' => 'https://vimeo.com/245392027',
            ],
        ];
    }

    /**
     * @dataProvider createBookmarkIsSuccessfulDataProvider
     */
    public function testCreateBookmarkIsSuccessful(string $url): void
    {
        $this->client->request('POST', '/bookmarks', [
            'url' => $url,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(204);
    }

    /**
     * @depends testCreateBookmarkIsSuccessful
     */
    public function testGetBookmarksIsSuccessful(): void
    {
        $this->client->request('GET', '/bookmarks');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response->headers->get('content-type'), 'application/json');
        $this->assertGreaterThanOrEqual(1, \count(json_decode($response->getContent(), true)), 'Empty response');
    }

    /**
     * @depends testGetBookmarksIsSuccessful
     */
    public function testDeleteBookmark(): void
    {
        $this->client->request('DELETE', '/bookmarks/1');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('DELETE', '/bookmarks/1');
        $this->assertResponseStatusCodeSame(404);
    }
}
