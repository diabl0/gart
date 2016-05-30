<?php
require_once 'vendor/autoload.php';
/*

// Dimmensions and metrics
// https://www.googleapis.com/analytics/v3/metadata/ga/columns?pp=1
$client = new GuzzleHttp\Client();
$res = $client->request('GET', 'https://www.googleapis.com/analytics/v3/metadata/ga/columns?pp=1');
$body = $res->getBody();

$data = json_decode($body);
$tmp = [];
$attr = [];

foreach ($data->items as $key => $item) {
    $tmp[$item->attributes->type][] = $item;
    
    $attr = array_merge($attr, array_keys((array)$item->attributes));
}
$attr = array_unique($attr);
echo '<pre>'; print_r( $attr ); echo '</pre>';exit();

echo '<pre>'; print_r( $tmp ); echo '</pre>';exit();
$metrics = implode(',', array_slice( $tmp['METRIC'], 1, 10) );
echo '<pre>'; print_r( $metrics ); echo '</pre>';
exit();
*/


/** @var string $metrics GA Metrics */
$allMetrics = [
    [
        'ga:users',
        'ga:newUsers',
        'ga:percentNewSessions',
        'ga:sessionsPerUser',
    ],
    [
        'ga:sessions',
        'ga:bounces',
        'ga:bounceRate',
        'ga:sessionDuration',
        'ga:avgSessionDuration',
        'ga:hits',
    ],
    [
        'ga:entranceBounceRate',
        'ga:organicSearches',
    ],
    [
        'ga:goalStartsAll',
        'ga:goalCompletionsAll',
        'ga:goalValueAll',
        'ga:goalValuePerSession',
        'ga:goalConversionRateAll',
        'ga:goalAbandonsAll',
        'ga:goalAbandonRateAll',
    ],
    [
        'ga:pageValue',
        'ga:entrances',
        'ga:entranceRate',
        'ga:pageviews',
        'ga:pageviewsPerSession',
        'ga:uniquePageviews',
        'ga:timeOnPage',
        'ga:avgTimeOnPage',
        'ga:exits',
        'ga:exitRate',
    ],
    [
        'ga:searchResultViews',
        'ga:searchUniques',
        'ga:avgSearchResultViews',
        'ga:searchSessions',
        'ga:percentSessionsWithSearch',
        'ga:searchDepth',
        'ga:avgSearchDepth',
        'ga:searchRefinements',
        'ga:percentSearchRefinements',
        'ga:searchDuration',
        'ga:avgSearchDuration',
        'ga:searchExits',
        'ga:searchExitRate',
        'ga:searchGoalConversionRateAll',
        'ga:goalValueAllPerSearch',
    ],
    [
        'ga:pageLoadTime',
        'ga:pageLoadSample',
        'ga:avgPageLoadTime',
        'ga:domainLookupTime',
        'ga:avgDomainLookupTime',
        'ga:pageDownloadTime',
        'ga:avgPageDownloadTime',
        'ga:redirectionTime',
        'ga:avgRedirectionTime',
        'ga:serverConnectionTime',
        'ga:avgServerConnectionTime',
        'ga:serverResponseTime',
        'ga:avgServerResponseTime',
        'ga:speedMetricsSample',
        'ga:domInteractiveTime',
        'ga:avgDomInteractiveTime',
        'ga:domContentLoadedTime',
        'ga:avgDomContentLoadedTime',
        'ga:domLatencyMetricsSample',
    ],
    [
        'ga:screenviews',
        'ga:uniqueScreenviews',
        'ga:screenviewsPerSession',
        'ga:timeOnScreen',
        'ga:avgScreenviewDuration',
    ],
    [
        'ga:uniqueEvents',
        'ga:totalEvents',
        'ga:eventValue',
        'ga:avgEventValue',
        'ga:sessionsWithEvent',
        'ga:eventsPerSessionWithEvent',
    ],
    [
        'ga:transactions',
        'ga:transactionsPerSession',
        'ga:transactionRevenue',
        'ga:revenuePerTransaction',
        'ga:transactionRevenuePerSession',
        'ga:transactionShipping',
        'ga:transactionTax',
        'ga:totalValue',
        'ga:itemQuantity',
        'ga:uniquePurchases',
        'ga:revenuePerItem',
        'ga:itemRevenue',
        'ga:itemsPerPurchase',
        'ga:localTransactionRevenue',
        'ga:localTransactionShipping',
        'ga:localTransactionTax',
        'ga:localItemRevenue',
        'ga:buyToDetailRate',
        'ga:cartToDetailRate',
        'ga:internalPromotionCTR',
        'ga:internalPromotionClicks',
        'ga:internalPromotionViews',
        'ga:localProductRefundAmount',
        'ga:localRefundAmount',
        'ga:productAddsToCart',
        'ga:productCheckouts',
        'ga:productDetailViews',
        'ga:productListCTR',
        'ga:productListClicks',
        'ga:productListViews',
        'ga:productRefundAmount',
        'ga:productRefunds',
        'ga:productRemovesFromCart',
        'ga:productRevenuePerPurchase',
        'ga:quantityAddedToCart',
        'ga:quantityCheckedOut',
        'ga:quantityRefunded',
        'ga:quantityRemovedFromCart',
        'ga:refundAmount',
        'ga:revenuePerUser',
        'ga:totalRefunds',
        'ga:transactionsPerUser',
    ],
    [
        'ga:socialInteractions',
        'ga:uniqueSocialInteractions',
        'ga:socialInteractionsPerSession',
    ],
    [
        'ga:userTimingValue',
        'ga:userTimingSample',
        'ga:avgUserTimingValue',
    ],
    [
        'ga:adsenseRevenue',
        'ga:adsenseAdUnitsViewed',
        'ga:adsenseAdsViewed',
        'ga:adsenseAdsClicks',
        'ga:adsensePageImpressions',
        'ga:adsenseCTR',
        'ga:adsenseECPM',
        'ga:adsenseExits',
        'ga:adsenseViewableImpressionPercent',
        'ga:adsenseCoverage',
    ],
    [
        'ga:adxImpressions',
        'ga:adxCoverage',
        'ga:adxMonetizedPageviews',
        'ga:adxImpressionsPerSession',
        'ga:adxViewableImpressionsPercent',
        'ga:adxClicks',
        'ga:adxCTR',
        'ga:adxRevenue',
        'ga:adxRevenuePer1000Sessions',
        'ga:adxECPM',
    ],
];

$requests = 0;

$merged = [];

foreach ($allMetrics as $key => $metrics) {
    $metricsCount = count($metrics);
    $offset = 0;
    do {
        $currentMetrics = implode(',', array_slice($metrics, $offset, 10));
        $offset += 10;

        $data = fetchData($currentMetrics);

        foreach ($data['rows'] as $row) {
            if (isset($merged[$row['ga:date'].$row['ga:hour'].$row['ga:minute']])) {
                $merged[$row['ga:date'].$row['ga:hour'].$row['ga:minute']] = array_merge(
                    $merged[$row['ga:date'].$row['ga:hour'].$row['ga:minute']],
                    $row
                );
            } else {
                $merged[$row['ga:date'].$row['ga:hour'].$row['ga:minute']] = $row;
            }
        }

    } while ($offset < $metricsCount);
}
echo '<pre>'; print_r( $merged ); echo '</pre>';exit();


function fetchData($metrics)
{

    /**
     * GA Credentials
     */
    $client_email = 'analytics@api-project-944302672487.iam.gserviceaccount.com';
    $private_key = file_get_contents('API Project-7e4581c3b3ca.p12');
    $scopes = ['https://www.googleapis.com/auth/analytics.readonly'];
    $credentials = new Google_Auth_AssertionCredentials(
        $client_email,
        $scopes,
        $private_key,
        'notasecret'
    );

    $client = new Google_Client();
    $client->setAssertionCredentials($credentials);

// Create Google Service Analytics object with our preconfigured Google_Client
    $analytics = new Google_Service_Analytics($client);

// Add Analytics View ID, prefixed with "ga:"
    $analyticsViewId = 'ga:3918249';


    /** @var integer $perPage Results per request */
    $perPage = 1000;

    $startDate = '2016-04-01';
    $endDate = '2016-04-01';

    $params = [
        'dimensions'  => 'ga:date,ga:hour,ga:minute',
        'max-results' => $perPage,
        'start-index' => 1,
    ];

    $totalResults = 0;
    $itemsCount = 0;
    $output = [];
    do {
        // Check Access token and reauthorize if needed.
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        // Fetch GA data
        $data = $analytics->data_ga->get($analyticsViewId, $startDate, $endDate, $metrics, $params);
        $totalResults = $data->getTotalResults();
        $rows = $data->getRows();
        $columnHeaders = $data->columnHeaders;

        $GLOBALS['requests']++;

        // Iterate over rows to make it more usable in future
        if (!empty($rows)) {
            foreach ($rows as $key1 => $row) {
                $outRow = [];
                foreach ($columnHeaders as $key2 => $column) {
                    $outRow[$column->name] = $row[$key2];
                }
                $output[] = $outRow;
            }
        }

        // Increase counters
        $itemsCount += count($rows);
        $params['start-index'] += $perPage;
    } while ($itemsCount < $totalResults);

// Cleanup column Headers
    foreach ($columnHeaders as $key => $column) {
        $columnHeaders[$key] = (array)$column;
    }

// Prepare result table
// @todo use objects
    $result = [
        'totalsForAllResults' => $data->totalsForAllResults,
        'columnHeaders'       => (array)$columnHeaders,
        'rows'                => $output,
    ];

    return $result;
}

echo '<pre>REQUESTS: '; print_r( $requests ); echo '</pre>';