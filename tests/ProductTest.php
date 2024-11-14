<?php

use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
  protected $baseUrl = 'http://localhost/Projets/JumpApi/?model=product';

  public function testGetProductById()
  {
    $response = file_get_contents($this->baseUrl . '&id=1');
    $data = json_decode($response, true);

    $this->assertEquals('success', $data['status']);
    $this->assertIsArray($data['data']);
    $this->assertArrayHasKey('id', $data['data']);
    $this->assertEquals(1, $data['data']['id']);
  }

  public function testGetNonExistentProductById()
  {
    $response = file_get_contents($this->baseUrl . '&id=9999');
    $data = json_decode($response, true);

    $this->assertEquals('error', $data['status']);
    $this->assertEquals('Product not found', $data['data']['message']);
  }

  // Removed delete tests as per your request

  public function testGetAllProducts()
  {
    $response = file_get_contents($this->baseUrl);
    $data = json_decode($response, true);

    $this->assertEquals('success', $data['status']);
    $this->assertIsArray($data['data']); // Check if 'data' is an array
  }

  public function testCreateProduct()
  {
    $data = json_encode([
      'name' => 'New Product',
      'price' => 99.99,
      'quantity' => 10
    ]);

    $context = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data,
      ],
    ]);

    $response = file_get_contents($this->baseUrl, false, $context);
    $responseData = json_decode($response, true);

    $this->assertEquals('success', $responseData['status']);
    $this->assertEquals('Resource created successfully', $responseData['data']['message']);
  }

  public function testCreateProductWithValidationErrors()
  {
    $data = json_encode([
      'name' => '',
      'price' => -10,
      'quantity' => 'not a number'
    ]);

    $context = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data,
      ],
    ]);

    $response = file_get_contents($this->baseUrl, false, $context);
    $responseData = json_decode($response, true);

    $this->assertEquals('error', $responseData['status']);
    $this->assertArrayHasKey('error', $responseData['data']);
  }

  public function testUpdateProduct()
  {
    $data = json_encode([
      'name' => 'Updated Product',
      'price' => 89.99,
      'quantity' => 5
    ]);

    $context = stream_context_create([
      'http' => [
        'method' => 'PUT',
        'header' => 'Content-Type: application/json',
        'content' => $data,
      ],
    ]);

    $response = file_get_contents($this->baseUrl . '&id=1', false, $context);
    $responseData = json_decode($response, true);

    $this->assertEquals('success', $responseData['status']);
    $this->assertEquals('Resource updated successfully', $responseData['data']['message']);
  }

  public function testUpdateNonExistentProduct()
  {
    $data = json_encode([
      'name' => 'Updated Product',
      'price' => 89.99,
      'quantity' => 5
    ]);

    $context = stream_context_create([
      'http' => [
        'method' => 'PUT',
        'header' => 'Content-Type: application/json',
        'content' => $data,
      ],
    ]);

    $response = file_get_contents($this->baseUrl . '&id=9999', false, $context);
    $responseData = json_decode($response, true);

    $this->assertEquals('error', $responseData['status']);
    $this->assertEquals('Product not found', $responseData['data']['message']);
  }
  /*   public function testSimulatedDeleteProduct()
  {
    // Simulate deleting a product with ID 4
    $response = file_get_contents($this->baseUrl . '&id=1', false, stream_context_create([
      'http' => [
        'method' => 'DELETE',
      ],
    ]));
    $data = json_decode($response, true);

    // Simulate expected response for deletion
    $this->assertEquals('success', $data['status']);
    $this->assertEquals('Resource deleted successfully', $data['data']['message']);

    // Optionally, you can check if the product still exists (simulating the database)
    // This step is just for simulation; you may want to adjust according to your test strategy
    $checkResponse = file_get_contents($this->baseUrl . '&id=1');
    $checkData = json_decode($checkResponse, true);
    $this->assertEquals('error', $checkData['status']);
    $this->assertEquals('Product not found', $checkData['data']['message']);
  } */

  public function testDeleteNonExistentProduct()
  {
    $response = file_get_contents($this->baseUrl . '&id=9999', false, stream_context_create([
      'http' => [
        'method' => 'DELETE',
      ],
    ]));
    $data = json_decode($response, true);
    var_dump($data);

    $this->assertEquals('error', $data['status']);
    $this->assertEquals('Product not found', $data['data']['message']);
  }
}
