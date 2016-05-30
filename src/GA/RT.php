<?php
namespace Luxoft\GA;

class RT extends AbstractClient
{

    /**
     * Fetch data from GART
     *
     * @param array $dimensions
     * @return array parsed received data
     */
    public function fetchData($dimensions)
    {
        // Create Google Service Analytics object with our preconfigured Google_Client
        $analytics = new \Google_Service_Analytics($this->getClient());

        /**
         * 1.Create and Execute a Real Time Report
         * An application can request real-time data by calling the get method on the Analytics service object.
         * The method requires an ids parameter which specifies from which view (profile) to retrieve data.
         * For example, the following code requests real-time data for view (profile) ID 56789.
         */

        $optParams = [
            'dimensions' => implode(',', array_slice($dimensions, 0, 7)),
            'sort'       => '-rt:activeUsers',
        ];

        $data = $analytics->data_realtime->get(
            'ga:'.$this->viewId,
            'rt:activeUsers',
            $optParams
        );

        return $this->formatData($data);

    }

    /**
     * Parse and format data for better visualization
     *
     * @param \Google_Service_Analytics_RealtimeData $data
     * @return array
     */
    protected function formatData($data)
    {
        $rows = $data->getRows();
        /** @noinspection PhpUndefinedFieldInspection */
        $columnHeaders = $data->columnHeaders;

        $output = [];

        // Iterate over rows to make it more usable in future
        if (!empty($rows)) {
            foreach ($rows as $key1 => $row) {
                $outRow = [];
                foreach ($columnHeaders as $key2 => $column) {
                    $outRow[$column->name] = $row[$key2];
                    if ($column->name == 'rt:activeUsers') {
                        $outRow[$column->name] = (int)$row[$key2];
                    }
                }
                $output[] = $outRow;
            }
        }


        // Cleanup column Headers
        foreach ($columnHeaders as $key => $column) {
            $columnHeaders[$key] = (array)$column;
        }

        // Prepare result table
        // @todo use objects?
        $result = [
            'totalsForAllResults' => $data->totalsForAllResults,
            'columnHeaders'       => (array)$columnHeaders,
            'rows'                => $output,
        ];

        return $result;


    }
}
