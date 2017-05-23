<?php

namespace Islandora\Lameduck\Controller;

use GuzzleHttp\Psr7\StreamWrapper;
use Islandora\Crayfish\Commons\CmdExecuteService;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class LameduckController
 * @package Islandora\Lameduck\Controller
 */
class LameduckController
{

    /**
     * @var \Islandora\Crayfish\Commons\CmdExecuteService
     */
    protected $cmd;

    /**
     * @var string
     */
    protected $executable;

    /**
     * @var \Monolog\Logger
     */
    protected $log;

    /**
     * Controller constructor.
     * @param \Islandora\Crayfish\Commons\CmdExecuteService $cmd
     * @param string $executable
     * @param $log
     */
    public function __construct(
        CmdExecuteService $cmd,
        $executable,
        $log
    ) {
        $this->cmd = $cmd;
        $this->executable = $executable;
        $this->log = $log;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $fedora_resource
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function lame(ResponseInterface $fedora_resource, Request $request)
    {
        $this->log->info('Lame request.');

        $status = $fedora_resource->getStatusCode();
        if ($status != 200) {
            $this->log->debug("Fedora Resource: ", [
              'body' => $fedora_resource->getBody(),
              'status' => $fedora_resource->getStatusCode(),
              'headers' => $fedora_resource->getHeaders()
            ]);
            return new Response(
                $fedora_resource->getReasonPhrase(),
                $status
            );
        }

        // Get audio as a resource.
        $body = StreamWrapper::getResource($fedora_resource->getBody());
        $this->log->debug("Fedora Resource: ", [
            $fedora_resource->getStatusCode()
        ]);

        // Arguments to lame are sent as a custom header
        $args = $request->headers->get('X-Islandora-Args');
        $this->log->debug("X-Islandora-Args:", ['args' => $args]);

        // Build arguments
        $cmd_string = "$this->executable $args - -";
        $this->log->info('Lame Command:', ['cmd' => $cmd_string]);

        // Return response.
        try {
            return new StreamedResponse(
                $this->cmd->execute($cmd_string, $body),
                200,
                ['Content-Type' => 'audio/mpeg'],
                $this->log->debug("Fedora Resource: ", [
                  'status' => $fedora_resource->getStatusCode()
                ])
            );
        } catch (\RuntimeException $e) {
            $this->log->error("RuntimeException:", ['exception' => $e]);
            return new Response($e->getMessage(), 500);
        }
    }
}
