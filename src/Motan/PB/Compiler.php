<?php

namespace Motan\PB;

use DrSlump\Protobuf;

/**
 * Compiler for PHP 5.4+
 * 
 * <pre>
 * Compiler
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-10-03]
 */
class Compiler extends Protobuf\Compiler
{
    public function compile($data)
    {
        // Parse the request
        $req = new \google\protobuf\compiler\CodeGeneratorRequest($data);

        // Set default generator class
        // $generator = '\DrSlump\Protobuf\Compiler\PhpGenerator';
        $generator = '\Motan\PB\BinaryGenerator';

        // Reset comments parser
        $this->comments->reset();
        $parseComments = false;

        // Get plugin arguments
        if ($req->hasParameter()) {
            parse_str($req->getParameter(), $args);
            foreach ($args as $arg => $val) {
                switch ($arg) {
                    case 'verbose':
                        $this->verbose = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        break;
                    case 'weibo':
                        $this->notice("Using Weibo generator");
                        $generator = '\Motan\PB\WeiboGenerator';
                        break;
                    case 'weibojson':
                        $this->notice("Using WeiboJson generator");
                        $generator = '\Motan\PB\WeiboJsonGenerator';
                        break;
                    case 'json':
                        $this->notice("Using ProtoJson generator");
                        $generator = __CLASS__ . '\JsonGenerator';
                        break;
                    case 'comments':
                        $parseComments = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'protos':
                        $this->protos = $val;
                        break;
                    case 'skip-imported':
                        $this->skipImported = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'options':
                        $this->options = $val;
                        break;
                    default:
                        $this->warning('Skipping unknown option ' . $arg);
                }
            }
        }

        // Parse comments if we're told to do so
        if ($parseComments) {
            if (empty($this->protos)) {
                throw new \RuntimeException('Unable to port comments if .proto files are not passed as argument');
            }
            foreach ($this->protos as $fname) {
                $src = file_get_contents($fname);
                if (false === $src) {
                    throw new \RuntimeException('Unable to parse file ' . $fname . ' for comments');
                }
                $this->comments->parse($src);
            }
        }

        /** @var $generator \DrSlump\Protobuf\Compiler\AbstractGenerator */
        $generator = new $generator($this);

        // Setup response object
        $resp = new \google\protobuf\compiler\CodeGeneratorResponse();

        // First iterate over all the protos to get a map of namespaces
        $this->packages = array();
        foreach ($req->getProtoFileList() as $proto) {
            $package = $proto->getPackage();
            $namespace = $generator->getNamespace($proto);
            if (isset($this->packages[$package]) && $namespace !== $this->packages[$package]) {
                $this->warning("Package $package was already mapped to {$this->packages[$package]} but has now been overridden to $namespace");
            }
            $this->packages[$package] = $namespace;
            $this->notice("Mapping $package to $namespace");
        }

        // Get the list of files to generate
        $files = $req->getFileToGenerate();

        // Run each file
        foreach ($req->getProtoFileList() as $file) {
            // Only compile those given to generate, not the imported ones
            if ($this->skipImported && !in_array($file->getName(), $files)) {
                $this->notice('Skipping generation of imported file "' . $file->getName() . '"');
                continue;
            }

            $sources = $generator->generate($file);
            foreach ($sources as $source) {
                $this->notice('Generating "' . $source->getName() . '"');
                $resp->addFile($source);
            }
        }

        // Finally serialize the response object
        return $resp->serialize();
    }
}
