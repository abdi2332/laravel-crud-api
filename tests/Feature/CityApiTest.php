<?php

namespace Tests\Feature;

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_city(): void
    {
        $response = $this->postJson('/api/cities', [
            'name' => 'Berlin',
            'country' => 'Germany',
            'population' => 3645000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Berlin')
            ->assertJsonPath('population', 3645000);

        $this->assertDatabaseHas('cities', ['name' => 'Berlin', 'country' => 'Germany']);
    }

    public function test_it_rejects_an_invalid_city(): void
    {
        $this->postJson('/api/cities', ['name' => 'Berlin'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['country']);
    }

    public function test_it_updates_and_deletes_a_city(): void
    {
        $city = City::query()->create([
            'name' => 'Berlin',
            'country' => 'Germany',
            'population' => 3645000,
        ]);

        $this->patchJson("/api/cities/{$city->id}", ['population' => 3700000])
            ->assertOk()
            ->assertJsonPath('population', 3700000);

        $this->deleteJson("/api/cities/{$city->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }

    public function test_it_returns_not_found_for_a_missing_city(): void
    {
        $this->getJson('/api/cities/999')->assertNotFound();
    }
}
