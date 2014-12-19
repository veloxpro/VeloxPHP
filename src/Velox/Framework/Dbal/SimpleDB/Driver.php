<?php
namespace Velox\Framework\Dbal\SimpleDB;

use Velox\Framework\Dbal\SimpleDB\Exception\QueryFailedException;

class Driver {
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    protected $accessKey;
    protected $secretKey;
    protected $httpScheme;
    protected $apiUrl;
    protected $nextToken;

    public function __construct($httpScheme, $apiUrl, $accessKey, $secretKey) {
        $this->httpScheme = $httpScheme;
        $this->apiUrl = $apiUrl;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function getAccessKey() {
        return $this->accessKey;
    }

    public function setAccessKey($accessKey) {
        $this->accessKey = $accessKey;
        return $this;
    }

    public function getSecretKey() {
        return $this->secretKey;
    }

    public function setSecretKey($secretKey) {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function getHttpScheme() {
        return $this->httpScheme;
    }

    public function setHttpScheme($httpScheme) {
        $this->httpScheme = $httpScheme;
        return $this;
    }

    public function getApiUrl() {
        return $this->apiUrl;
    }

    public function setApiUrl($apiUrl) {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    public function getNextToken() {
        return $this->nextToken;
    }

    public function listDomains($maxNumber = null, $nextToken = null) {
        $params = array('Action' => 'ListDomains');
        if ($maxNumber !== null)
            $params['MaxNumberOfDomains'] = $maxNumber;
        if ($nextToken !== null)
            $params['NextToken'] = $nextToken;
        $xml = $this->request($params);
        if (count($xml->ListDomainsResult->NextToken) > 0) {
            $this->nextToken = (String) $xml->ListDomainsResult->NextToken;
        }

        $domains = array();
        foreach ($xml->ListDomainsResult->DomainName as $domain)
            $domains[] = (String) $domain;
        return $domains;
    }

    public function createDomain($name) {
        if (!preg_match('/^[a-zA-Z0-9-_\.]{3,255}$/', $name))
            throw new Exception\QueryFailedException("Wrong domain name");
        $params = array(
            'Action' => 'CreateDomain',
            'DomainName' => $name,
        );
        $this->request($params);
    }

    public function deleteDomain($name) {
        if (!preg_match('/^[a-zA-Z0-9-_\.]{3,255}$/', $name))
            throw new Exception\QueryFailedException("Wrong domain name");
        $params = array(
            'Action' => 'DeleteDomain',
            'DomainName' => $name,
        );
        $this->request($params);
    }

    public function domainMetadata($name) {
        if (!preg_match('/^[a-zA-Z0-9-_\.]{3,255}$/', $name))
            throw new Exception\QueryFailedException("Wrong domain name");
        $params = array(
            'Action' => 'DomainMetadata',
            'DomainName' => $name,
        );
        $xml = $this->request($params);

        $toReturn = array();
        $toReturn['ItemCount'] = (String) $xml->DomainMetadataResult->ItemCount;
        $toReturn['ItemNamesSizeBytes'] = (String) $xml->DomainMetadataResult->ItemNamesSizeBytes;
        $toReturn['AttributeNameCount'] = (String) $xml->DomainMetadataResult->AttributeNameCount;
        $toReturn['AttributeNamesSizeBytes'] = (String) $xml->DomainMetadataResult->AttributeNamesSizeBytes;
        $toReturn['AttributeValueCount'] = (String) $xml->DomainMetadataResult->AttributeValueCount;
        $toReturn['AttributeValuesSizeBytes'] = (String) $xml->DomainMetadataResult->AttributeValuesSizeBytes;
        $toReturn['Timestamp'] = (String) $xml->DomainMetadataResult->Timestamp;
        return $toReturn;
    }

    public function putAttributes($domainName, $itemName, array $keyValuePairs, $isReplace = true) {
        $params = array(
            'Action' => 'PutAttributes',
            'DomainName' => $domainName,
            'ItemName' => $itemName,
        );
        $i = 0;
        foreach ($keyValuePairs as $k => $v) {
            $i++;
            $params['Attribute.' . $i . '.Name'] = $k;
            $params['Attribute.' . $i . '.Value'] = $v;
        }
        $this->request($params);
    }

    public function getAttributes($domainName, $itemName) {
        $params = array(
            'Action' => 'GetAttributes',
            'DomainName' => $domainName,
            'ItemName' => $itemName,
        );
        $xml = $this->request($params);

        $toReturn = array();
        foreach ($xml->GetAttributesResult->Attribute as $a)
            $toReturn[(String) $a->Name] = (String) $a->Value;
        return $toReturn;
    }

    public function deleteAttributes($domainName, $itemName, array $keyValuePairs = array()) {
        $params = array(
            'Action' => 'DeleteAttributes',
            'DomainName' => $domainName,
            'ItemName' => $itemName,
        );

        foreach ($keyValuePairs as $k => $v)
            $params['Attribute.' . $k . '.Name'] = $v;

        $this->request($params);
    }

    public function select($expression) {
        $params = array(
            'Action' => 'Select',
            'SelectExpression' => $expression
        );
        $xml = $this->request($params);

        $toReturn = array();
        foreach ($xml->SelectResult->Item as $item) {
            $i = array();
            foreach ($item->Attribute as $a) {
                $i[(String) $a->Name] = (String) $a->Value;
            }
            $toReturn[(String) $item->Name] = $i;
        }
        return $toReturn;
    }

    public function request($params, $method = self::METHOD_GET) {
        // Add addition params
        $params['Version'] = '2009-04-15';
        $params['Timestamp'] = date('Y-m-d\TH:i:s+00:00');
        $params['SignatureVersion'] = 2;
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['AWSAccessKeyId'] = $this->accessKey;
        ksort($params);
        $q = http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        $s = '';
        $s .= strtoupper($method) . "\n";
        $s .= $this->apiUrl . "\n";
        $s .= "/\n";
        $s .= $q;

        // calculate signature
        $signature = '';
        if (function_exists("hash_hmac")) {
            $signature = hash_hmac("sha256", $s, $this->secretKey, true);
        } elseif (function_exists("mhash")) {
            $signature = mhash(MHASH_SHA256, $s, $this->secretKey);
        } else {
            throw new Exception\QueryFailedException("No hash function available!");
        }
        $signature = base64_encode($signature);
        $params['Signature'] = $signature;

        // build request url
        $url = $this->httpScheme . '://' . $this->apiUrl . '/';
        $url .= '?' . http_build_query($params, null, '&', PHP_QUERY_RFC3986);

        // make actual request
        if (!function_exists('curl_init'))
            throw new Exception\QueryFailedException("CURL not available available!");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $data = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // check for errors
        $xml = simplexml_load_string($data);
        if (count($xml->Errors) > 0) {
            $msg = '';
            foreach ($xml->Errors as $error) {
                $msg .= $error->Error->Code . ': ' . $error->Error->Message . "\n";
                $msg .= "Request params:\n";
                foreach($params as $k => $v)
                    $msg .= "$k = $v, ";
            }
            throw new Exception\QueryFailedException($msg);
        }

        return $xml;
    }
}
