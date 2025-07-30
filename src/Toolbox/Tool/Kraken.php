<?php

namespace App\Toolbox\Tool;

use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTool('kraken', description: 'Fetches property details from the Street Group Property Data API')]
final readonly class Kraken
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(KRAKEN)%')]
        ?string $apiKey = null
    ) {
        // Use the provided API key or the one from the environment variable
        $this->apiKey = $apiKey ?? '';
        $this->baseUrl = 'https://api.data.street.co.uk';
    }

    /**
     * Fetch property details from the Street Group Property Data API
     *
     * @param string $propertyId The property ID to fetch details for
     * @return string The property details as a JSON string
     */
    public function __invoke(string $propertyId): string
    {
        try {
            // Construct the endpoint
            $endpoint = "/property-data/properties/{$propertyId}";

            // Prepare the parameters with default fields
            $params = [];
            // Use the default comprehensive list of fields
            $params['fields[property]'] =
                'address,airport_noise,assets,coastal_erosion,connectivity,' .
                'construction_age_band,construction_materials,council_tax,education,' .
                'energy_performance,estimated_rental_values,estimated_value,' .
                'estimated_value_rounded,estimated_values,flood_risk,hmo,identities,' .
                'internal_area_square_metres,is_bungalow,is_on_the_market,' .
                'latest_transaction_date,listed_buildings_on_plot,localities,location,' .
                'market_statistics,nearby_completed_transactions,nearby_listed_buildings,' .
                'nearby_listings,nearby_planning_applications,number_of_bathrooms,' .
                'number_of_bedrooms,occupancy,outdoor_space,ownership,planning_applications,' .
                'plot,propensity_to_let_score,propensity_to_sell_score,property_listings,' .
                'property_type,restrictive_covenants,right_of_way,radon_risk_level,' .
                'nearby_scheduled_monuments,street_group_property_id,street_view,tenure,' .
                'title_deeds,transactions,transport,utilities,year_built';

            $data = json_decode($this->krakenRequest($endpoint, 'GET', $params)->getContent(), JSON_PRETTY_PRINT);

            // Make the request and convert the array response to a JSON string
            return json_encode($data['data']);
        } catch (\Exception $e) {
            return sprintf("Error fetching property details: %s", $e->getMessage());
        }
    }

    /**
     * Make a request to the Street Group Property Data API
     *
     * @param string $endpoint The API endpoint to call (without the base URL)
     * @param string $method HTTP method (GET, POST, etc.)
     * @param array|null $params URL parameters
     * @param array|null $data Form data
     * @param array|null $jsonData JSON data
     * @return \Symfony\Contracts\HttpClient\ResponseInterface The JSON response from the API as an array
     */
    private function krakenRequest(
        string $endpoint,
        string $method = 'GET',
        ?array $params = null,
        ?array $data = null,
        ?array $jsonData = null
    ): \Symfony\Contracts\HttpClient\ResponseInterface
    {
        // Ensure endpoint starts with a slash
        if (!str_starts_with($endpoint, '/')) {
            $endpoint = "/{$endpoint}";
        }

        // Full URL
        $url = "{$this->baseUrl}{$endpoint}";

        // Headers with API key
        $headers = [
            'x-api-key' => $this->apiKey,
            'key' => $this->apiKey
        ];

        // Prepare the request options
        $options = [
            'headers' => $headers
        ];

        if ($params) {
            $options['query'] = $params;
        }

        if ($data) {
            $options['body'] = $data;
        }

        if ($jsonData) {
            $options['json'] = $jsonData;
        }

        // Make the request
        // Return the JSON response
        return $this->httpClient->request($method, $url, $options);
    }
}
