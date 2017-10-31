<?php
/**
 * Copyright (c) 2009-2017. Weibo, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *             http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Motan\PB;

use DrSlump\Protobuf;
use google\protobuf as proto;

/**
 * WeiboJsonGenerator for PHP 5.4+
 * 
 * <pre>
 * WeiboJsonGenerator
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-10-03]
 */
class WeiboJsonGenerator extends Protobuf\Compiler\PhpGenerator
{
    public function generate(proto\FileDescriptorProto $proto)
    {
        parent::generate($proto);

        $this->components = array();
        $namespace = $proto->getPackage();

        // Generate Enums
        foreach ($proto->getEnumType() as $enum) {
            $src = $this->compileEnum($enum, $namespace);
            $this->addComponent($namespace, $enum->getName(), $src);
        }

        // Generate Messages
        foreach ($proto->getMessageType() as $msg) {
            $src = $this->compileMessage($msg, $namespace);
            $this->addComponent($namespace, $msg->getName(), $src);
        }

        // Generate services
        if ($this->getOption('generic_services') && count($proto->hasService())) :
            foreach ($proto->getServiceList() as $service) {
                $src = $this->compileService($service, $namespace);
                $this->addComponent($namespace, $service->getName(), $src);
            }
        endif;
        foreach ($proto->getServiceList() as $service) {
            $src = $this->compileStub($service, $namespace);
            $this->addComponent($namespace, $service->getName(), $src);
        }

        // Collect extensions
        if ($proto->hasExtension()) {
            foreach ($proto->getExtensionList() as $field) {
                $this->extensions[$field->getExtendee()][] = array($namespace, $field);
            }
        }

        // Dump all extensions found in this proto file
        if (count($this->extensions)) :
            $s[] = 'namespace {';
            foreach ($this->extensions as $extendee => $fields) {
                foreach ($fields as $pair) {
                    list($ns, $field) = $pair;
                    $s[] = $this->compileExtension($field, $ns, '  ');
                }
            }
            $s[] = '}';

            $src = implode(PHP_EOL, $s);

            // In multifile mode we output all the extensions in a file named after
            // the proto file, since it's not trivial or even possible in all cases
            // to include the extensions with the extended message file.
            $fname = pathinfo($proto->getName(), PATHINFO_FILENAME);
            $this->addComponent(null, $fname . '-extensions', $src);

            // Reset extensions for next proto file
            $this->extensions = array();
        endif;

        $files = array();
        if (!$this->getOption('multifile')) {
            $src = '';
            foreach ($this->components as $content) {
                $src .= $content;
            }
            $fname = pathinfo($proto->getName(), PATHINFO_FILENAME);
            $fname = str_replace('.', '/', $namespace) . "/" . $fname;
            $files[] = $this->buildFile($proto, $fname, $src);
        } else {
            foreach ($this->components as $ns => $content) {
                $fname = str_replace('\\', '/', $ns);
                $files[] = $this->buildFile($proto, $fname, $content);
            }
        }

        return $files;
    }

    protected function compileMessage(proto\DescriptorProto $msg, $ns)
    {
        $s = array();
        $s[] = "namespace " . $this->normalizeNS($ns) . " {";
        $s[] = "";
        $s[] = "  // @@protoc_insertion_point(scope_namespace)";
        $s[] = "  // @@protoc_insertion_point(namespace_$ns)";
        $s[] = "";

        $cmt = $this->compiler->getComment($ns . '.' . $msg->getName(), '   * ');
        if ($cmt) :
            $s[] = "  /**";
            $s[] = $cmt;
            $s[] = "   */";
        endif;

        // Compute a new namespace with the message name as suffix
        $ns .= '.' . $msg->getName();

        $s[] = '  class ' . $msg->getName() . ' extends \Motan\PB\WeiboJsonMessage {';
        $s[] = '';

        foreach ($msg->getField() as $field) :
            $s[] = $this->generatePublicField($field, $ns, "    ");
        endforeach;
        $s[] = '';

        $s[] = '    /** @var \Closure[] */';
        $s[] = '    protected static $__extensions = array();';
        $s[] = '';
        $s[] = '    public static function descriptor()';
        $s[] = '    {';
        $s[] = '      $descriptor = new \DrSlump\Protobuf\Descriptor(__CLASS__, \'' . $ns . '\');';
        $s[] = '';
        foreach ($msg->getField() as $field) :
            $s[] = $this->compileField($field, $ns, "      ");
            $s[] = '      $descriptor->addField($f);';
            $s[] = '';
        endforeach;
        $s[] = '      foreach (self::$__extensions as $cb) {';
        $s[] = '        $descriptor->addField($cb(), true);';
        $s[] = '      }';
        $s[] = '';
        $s[] = '      // @@protoc_insertion_point(scope_descriptor)';
        $s[] = '      // @@protoc_insertion_point(descriptor_' . $ns . ')';
        $s[] = '';
        $s[] = '      return $descriptor;';
        $s[] = '    }';
        $s[] = '';

        //$s[]= "    protected static \$__exts = array(";
        //foreach ($msg->getExtensionRange() as $range):
        //$s[]= '      array(' . $range->getStart() . ', ' . ($range->getEnd()-1) . '),';
        //endforeach;
        //$s[]= "    );";
        //$s[]= "";

        foreach ($msg->getField() as $field) :
            $s[] = $this->generateAccessors($field, $ns, "    ");
        endforeach;

        $s[] = "";
        $s[] = "    // @@protoc_insertion_point(scope_class)";
        $s[] = '    // @@protoc_insertion_point(class_' . $ns . ')';
        $s[] = "  }";
        $s[] = "}";
        $s[] = "";

        // Generate Enums
        if ($msg->hasEnumType()) :
            foreach ($msg->getEnumType() as $enum) :
                $src = $this->compileEnum($enum, $ns);
                $this->addComponent($ns, $enum->getName(), $src);
            endforeach;
        endif;

        // Generate nested messages
        if ($msg->hasNestedType()) :
            foreach ($msg->getNestedType() as $msg) :
                $src = $this->compileMessage($msg, $ns);
                $this->addComponent($ns, $msg->getName(), $src);
            endforeach;
        endif;

        // Collect extensions
        if ($msg->hasExtension()) {
            foreach ($msg->getExtensionList() as $field) {
                $this->extensions[$field->getExtendee()][] = array($ns, $field);
            }
        }

        return implode(PHP_EOL, $s) . PHP_EOL;
    }

    protected function compileStub(proto\ServiceDescriptorProto $service, $ns)
    {
        $s = array();
        $s[] = 'namespace ' . $this->normalizeNS($ns) . ' {';
        $s[] = '';
        $s[] = "  // @@protoc_insertion_point(scope_namespace)";
        $s[] = "  // @@protoc_insertion_point(namespace_$ns)";
        $s[] = '';

        $cmt = $this->compiler->getComment($ns . '.' . $service->getName(), '   * ');
        if ($cmt) {
            $s[] = "  /**";
            $s[] = $cmt;
            $s[] = "   */";
        }
        $s[] = '  class ' . $service->getName() . ' extends \Grpc\BaseStub {';
        $s[] = '';
        $s[] = '    public function __construct($hostname, $opts, $channel = null) {';
        $s[] = '      parent::__construct($hostname, $opts, $channel);';
        $s[] = '    }';

        foreach ($service->getMethodList() as $method) {
            $ns_input = $this->normalizeNS($method->getInputType());
            $ns_output = $this->normalizeNS($method->getOutputType());
            $s[] = '    /**';

            $cmt = $this->compiler->getComment($ns . '.' . $service->getName() . '.' . $method->getName(), '     * ');
            if ($cmt) {
                $s[] = $cmt;
                $s[] = '     * ';
            }

            $s[] = '     * @param ' . $ns_input . ' $input';
            $s[] = '     */';
            $server_stream = $method->getServerStreaming();
            $client_stream = $method->getClientStreaming();
            $service_fqn = $ns . '.' . $service->getName();
            if ($client_stream) {
                if ($server_stream) {
                    $s[] = '    public function ' . $method->getName() . '($metadata = array(), $options = array()) {';
                    $s[] = '      return $this->_bidiRequest(\'/' . $service_fqn . '/' . $method->getName() . '\', \'\\' . $ns_output . '::deserialize\', $metadata, $options);';
                } else {
                    $s[] = '    public function ' . $method->getName() . '($metadata = array(), $options = array()) {';
                    $s[] = '      return $this->_clientStreamRequest(\'/' . $service_fqn . '/' . $method->getName() . '\', \'\\' . $ns_output . '::deserialize\', $metadata, $options);';
                }
            } else {
                if ($server_stream) {
                    $s[] = '    public function ' . $method->getName() . '($argument, $metadata = array(), $options = array()) {';
                    $s[] = '      return $this->_serverStreamRequest(\'/' . $service_fqn . '/' . $method->getName() . '\', $argument, \'\\' . $ns_output . '::deserialize\', $metadata, $options);';
                } else {
                    $s[] = '    public function ' . $method->getName() . '(\\' . $ns_input . ' $argument, $metadata = array(), $options = array()) {';
                    $s[] = '      return $this->_simpleRequest(\'/' . $service_fqn . '/' . $method->getName() . '\', $argument, \'\\' . $ns_output . '::deserialize\', $metadata, $options);';
                }
            }
            $s[] = '    }';
        }
        $s[] = '  }';
        $s[] = '}';

        return implode(PHP_EOL, $s) . PHP_EOL;
    }
}
