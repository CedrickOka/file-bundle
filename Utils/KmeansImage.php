<?php
namespace Oka\FileBundle\Utils;

/**
 * Class KmeansImage
 *
 * Here is an example:
 * <code>
 * <?php
 * $kmeans = new KmeansImage('image.png', 3);
 * $kmeans->execute();
 * $centroid = $kmeans->getDominantCentroid();
 * echo '#' . $centroid['hex'];
 * ?>
 * </code>
 *
 * @author Eric (MLM) <VisualPulse.net>
 * @author cedrick
 * @version 13.05.16
 */
class KmeansImage
{
    /**
     * Number of clusters that points are allocated to
     *
     * @var int
     */
    protected $k = 4;
    
    /**
     * Should we ignore the extremities in colors?
     *
     * If set to true, all values equal to or above $whiteLevel and all levels equal to or below $blacklevel are ignored from cluster averages
     *
     * @var bool
     */
    protected $ignoreExtremity = false;
    
    /**
     * Symmetric RGB value for black
     * <br>
     * ex. rgb(10, 10, 10)
     *
     * @constraint 0-255 && $blackValue < $whiteValue
     * @var int
     */
    protected $blackLevel = 10;
    
    /**
     * Symmetric RGB value for white
     * <br>
     * ex. rgb(245, 245, 245)
     *
     * @constraint 0-255 && $whiteValue > $blackValue
     * @var int
     */
    protected $whiteLevel = 245;
    
    /**
     * The initial colors we match all points to
     *
     * @var array
     */
    protected $initialCentroids;
    
    /**
     * The image data
     *
     * @var array
     */
    protected $imagePoints;
    
    /**
     * The dominant color
     *
     * Collective average of all colors in the cluster with greatest number of points in it
     *
     * @var array
     */
    protected $dominantCentroid;
    
    /**
     * The ending state of the clusters after the algorithm has converged
     *
     * @var array
     */
    protected $finalClusters;

    /**
     * Stores whether we have updated the centroid and clusters after changing some settings
     *
     * @var bool
     */
    protected $staleData = true;
    
    /**
     * For random development purposes
     *
     * @var mixed
     */
    public $debug;
    
    /**
     * Check whether we have updated the data since you changed a setting
     */
    public function isStale()
    {
        return $this->staleData;
    }
    
    /**
     * Returns K
     *
     * Number of clusters that colors are allocated to
     *
     * @return int
     */
    public function getK()
    {
        return $this->k;
    }
    
    /**
     * Set K
     *
     * Number of clusters that colors are allocated to
     *
     * @param int $value
     * @return bool
     */
    public function setK($value)
    {
        if($value > 0)
        {
            $this->k = $value;
            $this->staleData = true;
            return true;
        }

        return false;
    }
    
    /**
     * Returns all the information on the dominant centroid
     *
     * @return array
     */
    public function getDominantCentroid()
    {
        return $this->dominantCentroid;
    }
    
    /**
     * Returns all clusters after the algorithm converges
     *
     * @return array
     */
    public function getClusters()
    {
        return $this->finalClusters;
    }
    
    /**
     * Returns all clusters after the algorithm converges
     *
     * @return array
     */
    public function getImagePoints()
    {
        return $this->imagePoints;
    }
    
    /**
     *
     */
    public function ignoreExtremity($val)
    {
        $this->ignoreExtremity = $val;
        $this->staleData = true;
    }
    
    /**
     * Returns the Black Level
     *
     * Value at which we ignore all values <= if
     *
     * @return int
     */
    public function getBlackLevel()
    {
        return $this->blackLevel;
    }
    
    /**
     * Set the level equal to and below at which we ignore values
     * <br>
     * Only applies when $ignoreValues is true
     *
     * @param $val
     */
    public function setBlackLevel($val)
    {
        if($val >= 0 && $val <= 255)
        {
            $this->blackLevel = $val;

            if($this->ignoreExtremity)
                $this->staleData = true;
        }
    }
    
    /**
     * Returns the White Level
     *
     * Value at which we ignore all values <= if
     *
     * @return int
     */
    public function getWhiteLevel()
    {
        return $this->whiteLevel;
    }
    
    /**
     * Set the level equal to and above at which we ignore values
     * <br>
     * Only applies when $ignoreValues is true
     *
     * @param $val
     */
    public function setWhiteLevel($val)
    {
        if($val >= 0 && $val <= 255)
        {
            $this->whiteLevel = $val;

            if($this->ignoreExtremity)
                $this->staleData = true;
        }
    }
    
    /**
     * @param mixed $image Path to the image
     * @param int $k Number of color groups to form
     */
    function __construct($image, $k = 3) {
        $this->k = $k;

        $this->generateRGBPointsFromImage($image);
    }
    
    /**
     * Finds new initial colors to start from
     */
    public function reformInitialPoints()
    {
        $this->generateInititalCentroids($this->imagePoints, $this->k);
    }
    
    /**
     *
     *
     * @return array
     */
    public function execute()
    {
        $this->generateInititalCentroids($this->imagePoints, $this->k);

        // Initial Centroids
        $centroids = $this->initialCentroids;

        // We keep going until we have converged
        $rgbColorIndexs = array('r', 'g', 'b');

        $clusters = array();
        $converged = false;
        while(!$converged)
        {
            $clusters = array();
            $preCentroids = $centroids;

            // Set up the arrays
            foreach($centroids as $keyCentroid => $centroid)
            {
                $clusters[$keyCentroid]['count'] = 0;
                $clusters[$keyCentroid]['ignoreCount'] = 0;
                foreach($rgbColorIndexs as $rgbColorIndex)
                {
                    $clusters[$keyCentroid]['rgb_totals'][$rgbColorIndex] = 0;
                }
            }

            // Put each color into the cluster that it is closest to rgb distance wise
            foreach($this->imagePoints as $point)
            {
                $ignore = false;

                $distances = array();
                // Next we compare the color to all the centroids to find the nearest one
                foreach($centroids as $keyCentroid => $centroid)
                {
                    $distances[$keyCentroid] = $this->distance3d($point['rgb'], $centroid);
                }
                $closestKeyCentroid = array_keys($distances, min($distances));
                $closestKeyCentroid = $closestKeyCentroid[0];

                // If we are ignoring values and...
                // the point is not grayscale and above the black level but below the white level, then we should count it
                if(!$this->ignoreExtremity || ($this->ignoreExtremity && (!($point['rgb']['r'] == $point['rgb']['g'] && $point['rgb']['g'] == $point['rgb']['b']) || (($point['rgb']['r'] == $point['rgb']['g'] && $point['rgb']['g'] == $point['rgb']['b']) && $point['rgb']['r'] >= $this->blackLevel && $point['rgb']['r'] <= $this->whiteLevel))))
                {
                    foreach($rgbColorIndexs as $rgbColorIndex)
                    {
                        $clusters[$closestKeyCentroid]['rgb_totals'][$rgbColorIndex] += $point['rgb'][$rgbColorIndex];
                    }
                    $clusters[$closestKeyCentroid]['count']++;
                }
                // Otherwise, put it in the cluster but make it have no effect on the new means/averages
                else
                {
                    $ignore = true;
                    $clusters[$closestKeyCentroid]['ignoreCount']++;
                }

                // Add some extra data into the clusters array in case we want to use it later
                $clusters[$closestKeyCentroid]['points'][] = array(
                    'xCoord' => $point['xCoord'],
                    'yCoord' => $point['yCoord'],
                    'rgb' => $point['rgb'],
                    'hex' => $point['hex'],
                    'ignored' => $ignore,
                );
            }

            // Then we average the cluster to get a new centroid color that better represents the cluster
            foreach($clusters as $key => $cluster)
            {
                foreach($rgbColorIndexs as $rgbColorIndex)
                {
                    if($cluster['count'] == 0)
                    {
                        $centroids[$key][$rgbColorIndex] = 0;

                        // Add the cluster average rgb to the array
                        $clusters[$key]['rgb'][$rgbColorIndex] = 0;
                    }
                    else
                    {
                        $centroids[$key][$rgbColorIndex] = round($cluster['rgb_totals'][$rgbColorIndex]/$cluster['count']);

                        // Add the cluster average rgb to the array
                        $clusters[$key]['rgb'][$rgbColorIndex] = round($cluster['rgb_totals'][$rgbColorIndex]/$cluster['count']);
                    }
                }

                // Add the cluster average rgb to the array
                $clusters[$key]['hex'] = $this->RGBtoHex($clusters[$key]['rgb']);
            }


            // If the centroids are the same then we have converged and can stop the iterating
            if($preCentroids == $centroids)
                $converged = true;
        }


        // Find the the most weighted centroid
        $mainCentroidKey = -1;
        $mainCentroid = null;
        $weight = 0;
        foreach($clusters as $key => $cluster)
        {
            if($cluster['count'] > $weight)
            {
                $mainCentroidKey = $key;
                $mainCentroid = $centroids[$key];

                $weight = $cluster['count'];
            }
        }


        $this->finalClusters = $clusters;
        $this->dominantCentroid = array(
            'cluster_key' => $mainCentroidKey,
            'rgb' => $mainCentroid,
            'hex' => $this->RGBtoHex($mainCentroid),
        );

        $this->staleData = false;
    }
    
    /**
     * Generate RGB points from image
     * This function has been rewritten to support all types of image files
     * Modify by @author Cedrick Oka
     * 
     * @param $image
     */
    private function generateRGBPointsFromImage($image)
    {
    	if (!$image instanceof \Imagick) {
    		$image = new \Imagick($image);
    	}
    	
    	$points = [];
    	/** @var \ImagickPixelIterator $pixelIterator */
    	$pixelIterator = $image->getPixelIterator();
    	
    	foreach ($pixelIterator as $row => $pixels) {
    		/** @var \ImagickPixel $pixel */
    		foreach ($pixels as $col => $pixel) {
    			$rgb = $pixel->getColor();
    			unset($rgb['a']);
    			$points[] = [
    					'xCoord' 	=> $row,
    					'yCoord' 	=> $col,
    					'rgb' 		=> $rgb,
    					'hex' 		=> $this->RGBtoHex($rgb)
    			];
    		}
    	}
    	
    	$this->imagePoints = $points;
    }
    
    /**
     * Samples $K number of unique colors from the image randomly
     *
     * @param $points
     * @param int $K
     * @return array
     */
    private function generateInititalCentroids($points, $K = 4)
    {
        $centroids = array();
        while (count($centroids) < $K) {
            // Get a random key for our data
            $randKeys = array_rand($points, 1);

            // Check if the color is already in the initial centroids
            // We do not want repeat colors so only add it if it is not in there already
            if (!in_array($points[$randKeys]['rgb'], $centroids)) {
                $centroids[] = $points[$randKeys]['rgb'];
            }
        }

        $this->initialCentroids = $centroids;
    }
    
    /**
     * Calculates 3d euclidean distance
     *
     * @param $p
     * @param $q
     * @return float
     */
    private function distance3d($p, $q)
    {
        $distance = sqrt(pow(($p['r'] - $q['r']), 2) + pow(($p['g'] - $q['g']), 2) + pow(($p['b'] - $q['b']), 2));

        return $distance;
    }
    
    /**
     * Calculates hex code equivalent from rgb array of data
     *
     * @param $rgb
     * @return string
     */
    private function RGBtoHex($rgb)
    {
        // Convert to Hex
        $hex = '';
        if($rgb != null)
        {
            foreach($rgb as $color => $value)
            {
                // Find first character in the chunk
                $char[1] = floor($value / 16);

                // Now find the second character in the chunk
                $char[2] = floor($value % 16);

                foreach($char as $character => $base_10)
                {
                    $hex .= strval(base_convert($base_10, 10, 16));
                }
            }
        }
        // They gave us null, so return black...
        else
            $hex = '000000';

        return $hex;
    }
}