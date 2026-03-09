<?php

namespace FreedomtechHosting\FtLagoonPhp\ClientTraits;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;

trait OrganizationTrait 
{
        
    /**
     * @throws LagoonClientInitializeRequiredToInteractException
     */
    public function removeUserFromOrganizationGroups(int $orgId, string $userEmail): array
    {
        if (empty($this->lagoonToken) || empty($this->graphqlClient)) {
            throw new LagoonClientInitializeRequiredToInteractException;
        }

        $mutation = <<<GQL
            mutation {
                removeUserFromOrganizationGroups(input:  {
                    organization: {$orgId},
                    user:  {
                        email: "{$userEmail}"
                    }
                }){
                    id
                    name
                }
            }
        GQL;

        $response = $this->graphqlClient->query($mutation);

        if ($response->hasErrors()) {
            return ['error' => $response->getErrors()];
        } else {
            // Returns an array with all the data returned by the GraphQL server.
            return $response->getData();
        }
    }

}