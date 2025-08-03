<?php

namespace Dimafe6\BankID\Model;

use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractResponseModel
 *
 * Constructor convert ResponseInterface to object properties
 *
 * @category PHP
 * @package  Dimafe6\BankID\Model
 * @author   Dmytro Feshchenko <dimafe2000@gmail.com>
 */
class AbstractResponseModel
{
    /**
     * AbstractResponseModel constructor.
     * @param ResponseInterface|null $response
     */
    public function __construct(?ResponseInterface $response = null)
    {
        if (null !== $response) {
            $responseText  = $response->getBody()->getContents();
            $responseArray = (array)json_decode($responseText);

            foreach ($responseArray as $key => $value) {
                if (property_exists($this, $key)) {
                    // Handle special object types
                    if ($key === 'completionData' && $value instanceof \stdClass) {
                        $this->$key = $this->createCompletionData($value);
                    } else {
                        $this->$key = $value;
                    }
                } else {
                    // For backward compatibility, still allow dynamic properties
                    $this->$key = $value;
                }
            }
        }
    }

    private function createCompletionData(\stdClass $data): CompletionData
    {
        $completionData = new CompletionData();
        foreach (get_object_vars($data) as $key => $value) {
            $completionData->$key = $value;
        }
        return $completionData;
    }
}
