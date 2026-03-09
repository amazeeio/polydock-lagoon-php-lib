<?php

namespace FreedomtechHosting\FtLagoonPhp;

use FreedomtechHosting\FtLagoonPhp\ClientTraits\AuthTrait;
use FreedomtechHosting\FtLagoonPhp\ClientTraits\GroupTrait;
use FreedomtechHosting\FtLagoonPhp\ClientTraits\ProjectEnvironmentTrait;
use FreedomtechHosting\FtLagoonPhp\ClientTraits\ProjectTrait;
use FreedomtechHosting\FtLagoonPhp\ClientTraits\OrganizationTrait;
use Softonic\GraphQL\Client as GraphqlClient;
use Softonic\GraphQL\ClientBuilder;

/**
 * Client class for interacting with the Lagoon API
 *
 * This class provides methods to interact with Lagoon's GraphQL API, handling operations like:
 * - Project management (creation, deletion, deployment)
 * - Environment management
 * - Variable management
 * - Authentication
 *
 * It requires SSH key authentication and manages the GraphQL client connection.
 */
class Client
{
    protected GraphqlClient $graphqlClient;

    protected string $sshPrivateKeyFile;

    protected string $lagoonSshUser;

    protected string $lagoonSshServer;

    protected string $lagoonSshPort;

    protected ?string $lagoonToken = null;

    protected string $lagoonApiEndpoint;

    protected string|bool $debug = false;

    use AuthTrait;
    use GroupTrait;
    use OrganizationTrait;
    use ProjectEnvironmentTrait;
    use ProjectTrait;

    /**
     * Constructor for the Lagoon API client
     *
     * Initializes the client with configuration settings for SSH and API connectivity.
     * Uses default values for most settings if not explicitly provided.
     *
     * @param  array  $config  Configuration array with optional keys:
     *                         - ssh_user: SSH username (default:
     *                         'lagoon') - ssh_server: SSH server
     *                         hostname (default:
     *                         'ssh.lagoon.amazeeio.cloud') -
     *                         ssh_port: SSH port (default: '32222') -
     *                         endpoint: API endpoint URL (default:
     *                         'https://api.lagoon.amazeeio.cloud/graphql')
     *                         - ssh_private_key_file: Path to SSH
     *                         private key (default: '~/.ssh/id_rsa')
     *
     * @throws LagoonClientPrivateKeyNotFoundException
     */
    public function __construct(protected array $config = [])
    {
        $this->lagoonSshUser = $this->config['ssh_user'] ?? 'lagoon';
        $this->lagoonSshServer = $this->config['ssh_server'] ?? 'ssh.lagoon.amazeeio.cloud';
        $this->lagoonSshPort = $this->config['ssh_port'] ?? '32222';
        $this->lagoonApiEndpoint = $this->config['endpoint'] ?? 'https://api.lagoon.amazeeio.cloud/graphql';
        $this->sshPrivateKeyFile = $this->config['ssh_private_key_file'] ?? getenv('HOME').'/.ssh/id_rsa';

        if (! isset($this->config['debug'])) {
            $this->debug = false;
        } else {
            $this->debug = $this->config['debug'];
        }

        if (! file_exists($this->sshPrivateKeyFile)) {
            throw new LagoonClientPrivateKeyNotFoundException($this->sshPrivateKeyFile);
        }
    }

    /**
     * Set the debug mode
     *
     * @param  bool  $debug  True to enable debug, false to disable
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * Get the debug mode
     *
     * @return bool True if debug is enabled, false otherwise
     */
    public function getDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Initializes the GraphQL client with authentication token
     *
     * @throws LagoonClientTokenRequiredToInitializeException if no token is set
     */
    public function initGraphqlClient(): void
    {
        if (empty($this->lagoonToken)) {
            throw new LagoonClientTokenRequiredToInitializeException;
        }

        $this->graphqlClient = ClientBuilder::build(
            $this->lagoonApiEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->lagoonToken,
                ],
            ]
        );
    }

    /**
     * Gets the GraphQL client instance.
     *
     * @throws LagoonClientTokenRequiredToInitializeException if graphql client is not set
     */
    public function getGraphqlClient(): GraphqlClient
    {
        if (empty($this->lagoonToken) || empty($this->graphqlClient)) {
            throw new LagoonClientTokenRequiredToInitializeException;
        }

        return $this->graphqlClient;
    }

    /**
     * Sets the Lagoon authentication token
     *
     * @param  string  $token  The authentication token
     */
    public function setLagoonToken(string $token): void
    {
        $this->lagoonToken = $token;
    }

    /**
     * Gets the current Lagoon authentication token
     *
     * @return string|null The current token or null if not set
     */
    public function getLagoonToken(): ?string
    {
        return $this->lagoonToken;
    }
}
