<?php

/*
 * Cache class.
 * 
 * Read and update cache files dynamically.
 * Enable caching of simple key/value pairs, or complex arrays.
 * Ideally, the cache should be read before any attempt to connect to the DB, to save resources.
 * For now, only the most frequent queries are cached. (map and cron:currday)
 * See file "Entreprise/Technique/Analyse cache" for further uses and improvements.
 * The cache allows keeping data shared among the users. For data only accessed by one user, such as personal statistics, perfer using $_SESSION.
 * 1 cache file = 1 city. Other caches per city may be added w/ different timeouts.
 */

class Cache
{
    public $cache = '';
    public $searchfor = '';
    //public $infoMessage = '';
    public $id_city = 0;
    public $query = '';
    public $isCache = false;
    public $isData = false;
    public $cacheHasExpired = false;

    // Defines the name of the cache file. If the user is inside a city, the corresponding city cache is used.
    // TODO : add a general cache (for game news etc). The logic will need to be changed.
    public function __construct($id_city)
    {
        $this->cache = $this->getCacheName($id_city);
        $this->id_city = $id_city;
    }

    // There is on cache file per city.
    public function getCacheName($id_city)
    {
        $cache = 'cache'.$id_city.'.txt';
        return $cache;
    }

    // Create a cache file
    public function createCache($cache)
    {
        $file = fopen($cache, "w") or die("Unable to open cache file!");
        fclose($file);
        return true;
    }

    // Search for a string or an array in a cache file
    public function getCachedData($searchfor, $isArray)
    {
        $lines = file($this->cache);
        $data = '';

        // Get the 1st line matching the searched string (scans only before the "=" on each line)
        foreach($lines as $line)
        {
            //if(strpos(strtok($line, '='), $searchfor) !== false)
            if(strtok($line, '=') == $searchfor)
            {
                $data = $line;
                break;
            }
        }
        
        // Convert and retrun the data as a string
        if($isArray == false)
        {
            // Take only the value after the "="
            $data = substr($data, strpos($data, "=") + 1);
        }
        else if($isArray == true)
        {
            // Take only the JSON after "="
            $data = substr($data, strpos($data, "=") + 1);

            // Convert the JSON into array
            $data = json_decode($data, true); //"true" is necessary for JSON>Array conversion.
        }
        else
        {
            throw new Exception('Type non reconnu.');
        }
        return $data;
    }

    /**
     *  Adds a single variable as a string, or an array, at the beginning of a cache file.
     *  @param string $cache : the full file name. Ex : cache121.txt
     *  @param array $keyToCache : the string before the "=". It must be unique in the file.
     *  @param array $valueToCache : the string or JSON array to store after the "=".
     */
    public function addDataToCache($cache, $keyToCache, $valueToCache)
    {
        //If a single value to cache : Add a line at the beginning of the cache
        $t = gettype($valueToCache);

        // If the type of the data to store is incorrect
        if($t != 'string' && $t != 'array')
        {
            $valueToCache = (string)$valueToCache;
        }
        else if($t == "string")
        {
            $txt = $keyToCache."=".$valueToCache."\n";
            $txt .= file_get_contents($cache);
            file_put_contents($cache, $txt);
        }
        // If an array to cache : convert in JSON (on a single line)
        else if($t == 'array')
        {
            // Convert the value into JSON format
            $valueToCacheJSON = json_encode($valueToCache);

            // Remove the line breaks (totest w/out)
            $valueToCacheJSON = str_replace(array("\r", "\n"), '', $valueToCacheJSON);

            // Store at the beginning of the cache
            $txt = $keyToCache."=".$valueToCacheJSON."\n";
            $txt .= file_get_contents($cache);
            file_put_contents($cache, $txt);

            // If adding at the end of file, prefer
            // file_put_contents($cache, $txt, FILE_APPEND | LOCK_EX);
        }
        else
        {
            throw new Exception("Type non reconnu.");
        }

        // Cas lignes multiples avec DELIMITEUR (par ex : code HTML complet) 
        // > supprimer les espaces comme JSON !
    }

    // Does the cache exists ? If yes, is the timeout not crossed and the desired data are inside ? If OK : read desired data from cache.
    // If the cache does not exist, create if, make SQL query and store the data to cache. 
    // If it exists but is in timeout, delete it then create it then store the data from DB.
    // Finally, if the cache exists but the desired data is not inside, store it from DB.
    public function checkForDataInCache($searchfor, $isArray)
    {
        $cache = $this->getCacheName($this->id_city);

        if(file_exists($cache))
        {
            $this->isCache = true;

            $jourFichier = date("Ymd", filemtime($cache));
            $jourNow = date("Ymd");
            
            // Si moins de 24h
            if($jourFichier==$jourNow)
            {
                $this->isCache=true;
                $data = '';
                $data = $this->getCachedData($searchfor, $isArray);

                //$data = "12"
                if($data != '')
                {
                    // AssertDataType() ?
                    $this->isData = true;
                    //$this->infoMessage = 'data from cache';
                    return $data;
                }
                else
                {
                    $this->isData = false;
                }
            }
            //cache existe mais expriré
            else
            {
                //supprimer le cache
                //recréer le cache
                $this->isCache = true;
                $this->cacheHasExpired=true;
                $this->isData = false;
            }
        }
    }

    /**
     *  Updates the cache content by deleting, creating or updating the cache file based on conditions.
     */
    public function updateCache($keyToCache, $valueToCache)
    {
        // Delete the cache if is has expired
        if($this->isCache && $this->cacheHasExpired)
        {
            unlink($this->cache);
            $this->isCache = false;
            $this->cacheHasExpired = false;
        }
        // Create the cache
        if(!$this->isCache)
        {
            $this->createCache($this->cache);

            $this->isCache= true;
            $this->cacheHasExpired=false;
        }

        // Add data to cache
        if(!$this->isData)
        {
            $this->addDataToCache($this->cache, $keyToCache, $valueToCache);
            
            $this->isData = true;
            $this->isCache= true;
        }
        //$this->infoMessage = 'data from DB';
    }
}