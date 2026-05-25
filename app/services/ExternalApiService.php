<?php


declare(strict_types=1);

class ExternalApiService
{
    private ?string $lastError = null;

    public function getBookByISBN(string $isbn): ?array
    {
        $isbn = preg_replace('/[^0-9Xx]/', '', $isbn) ?? '';

        if ($isbn === '') {
            $this->lastError = 'ISBN is required.';
            return null;
        }

        $url = 'https://openlibrary.org/api/books?bibkeys=ISBN:' . urlencode($isbn) . '&format=json&jscmd=data';
        $response = $this->fetchJson($url);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);

        if (! is_array($data)) {
            $this->lastError = 'Open Library returned invalid JSON.';
            return null;
        }

        $book = $data['ISBN:' . $isbn] ?? null;

        if (! is_array($book)) {
            $this->lastError = 'Book not found in Open Library.';
            return null;
        }

        return [
            'title' => (string) ($book['title'] ?? ''),
            'author' => (string) ($book['authors'][0]['name'] ?? ''),
            'category' => (string) ($book['subjects'][0]['name'] ?? ''),
            'isbn' => $isbn,
            'description' => $this->extractDescription($book),
            'cover' => (string) ($book['cover']['large'] ?? $book['cover']['medium'] ?? $book['cover']['small'] ?? ''),
        ];
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    private function fetchJson(string $url): ?string
    {
        if (! function_exists('curl_init')) {
            $this->lastError = 'PHP cURL extension is not enabled.';
            return null;
        }

        $curl = curl_init($url);

        if ($curl === false) {
            $this->lastError = 'Could not initialize the API request.';
            return null;
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_USERAGENT => 'LibraryManagementSystem/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_PROXY => '',
        ]);

        $response = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($response === false || $statusCode < 200 || $statusCode >= 300) {
            $this->lastError = $curlError !== ''
                ? 'API request failed: ' . $curlError
                : 'API request failed with HTTP status ' . $statusCode . '.';
            return null;
        }

        return (string) $response;
    }

    private function extractDescription(array $book): string
    {
        $notes = $book['notes'] ?? '';

        if (is_string($notes) && $notes !== '') {
            return $notes;
        }

        if (is_array($notes) && isset($notes['value'])) {
            return (string) $notes['value'];
        }

        return 'No description available.';
    }
}
