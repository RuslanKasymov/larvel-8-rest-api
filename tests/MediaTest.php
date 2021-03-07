<?php

namespace Tests;

use App\Models\User;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\MediaTestTrait;

class MediaTest extends TestCase
{
    use MediaTestTrait;

    protected $admin;
    protected $user;
    protected $file;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        $this->admin = User::find(1);
        $this->user = User::find(2);
        $this->file = UploadedFile::fake()->image('file.png', 600, 600);
    }

    public function testCreate()
    {
        $response = $this->actingAs($this->admin)->json('post', '/media', ['file' => $this->file]);

        $response->assertStatus(Response::HTTP_CREATED);

        $responseData = $response->json();

        $this->assertDatabaseHas('media', [
            'id' => $responseData['id'],
            'is_public' => false,
            'name' => 'file.png',
            'owner_id' => $this->admin->id
        ]);
    }

    public function testCreatePublic()
    {
        $response = $this->actingAs($this->admin)->json(
            'post',
            '/media',
            ['file' => $this->file, 'is_public' => true]
        );

        $responseData = $response->json();

        $this->assertDatabaseHas('media', [
            'id' => $responseData['id'],
            'is_public' => true
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function testCreateNoAuth()
    {
        $response = $this->json('post', '/media', ['file' => $this->file]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testDelete()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/media/1');

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('media', [
            'id' => 1
        ]);
    }

    public function testDeleteNotExists()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/media/0');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteNoPermission()
    {
        $response = $this->actingAs($this->user)->json('delete', '/media/1');

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('media', [
            'id' => 1
        ]);
    }

    public function testDeleteAsOwnerImage()
    {
        $response = $this->actingAs($this->user)->json('delete', '/media/3');

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('media', [
            'id' => 3
        ]);
    }

    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/media/1');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->assertDatabaseHas('media', [
            'id' => 1
        ]);
    }

    public function getSearchFilters()
    {
        return [
            [
                'filter' => [
                    'per_page' => 2,
                    'page' => 2
                ],
                'result' => 'list_media_by_page_per_page.json'
            ]
        ];
    }

    /**
     * @dataProvider  getSearchFilters
     *
     * @param  array $filter
     * @param  string $fixture
     */
    public function testSearch($filter, $fixture)
    {
        $response = $this->actingAs($this->admin)->json('get', '/media', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }

    public function getBadFiles()
    {
        return [
            [
                'filter' => ['fileName' => 'notAVirus.exe']
            ],
            [
                'filter' => ['fileName' => 'notAVirus.psd']
            ]
        ];
    }

    /**
     * @dataProvider  getBadFiles
     *
     * @param  array $filter
     */
    public function testUploadingBadFiles($filter)
    {

        $this->file = UploadedFile::fake()->create($filter['fileName'], 1024);

        $response = $this->actingAs($this->user)->json('post', '/media', ['file' => $this->file]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJson([
            'errors' => [
                'file' => ['The file must be a file of type: jpg, jpeg, bmp, png.']
            ]
        ]);
    }

    public function getGoodFiles()
    {
        return [
            [
                'filter' => ['fileName' => 'image.jpg']
            ],
            [
                'filter' => ['fileName' => 'image.png']
            ],
            [
                'filter' => ['fileName' => 'image.bmp']
            ]
        ];
    }

    /**
     * @dataProvider  getGoodFiles
     *
     * @param  array $filter
     */
    public function testUploadingGoodFiles($filter)
    {
        $this->file = UploadedFile::fake()->image($filter['fileName'], 600, 600);

        $response = $this->actingAs($this->user)->json('post', '/media', ['file' => $this->file]);

        $responseData = $response->json();

        $this->assertDatabaseHas('media', [
            'id' => $responseData['id'],
        ]);
    }
}
