<?php

test_case("Request");
   test_case_describe("Testing limonade request functions.");
   
   function before_each_test_in_request()
   {
     env(null);
   }
   
   function test_request_methods()
   {
     $m = request_methods();
     assert_length_of($m, 5);
   }
   
   function test_request_method_is_allowed()
   {
     assert_true(request_method_is_allowed("GET"));
     assert_true(request_method_is_allowed("get"));
     assert_true(request_method_is_allowed("POST"));
     assert_true(request_method_is_allowed("PUT"));
     assert_true(request_method_is_allowed("DELETE"));
     assert_true(request_method_is_allowed("HEAD"));
   }
   
   function test_request_method()
   {
     $env = env();
     $env['SERVER']['REQUEST_METHOD'] = null;
     
     assert_trigger_error("request_method");
     
     $methods = request_methods();
     
     foreach($methods as $method)
     {
       $env['SERVER']['REQUEST_METHOD'] = $method;
       assert_equal(request_method($env), $method);
     }
     
     $env['SERVER']['REQUEST_METHOD'] = "POST";
     
     $env['POST']['_method'] = "PUT";
     assert_equal(request_method($env), "PUT");
     
     $env['POST']['_method'] = "DELETE";
     assert_equal(request_method($env), "DELETE");
     
     $env['POST']['_method'] = "UNKOWN";
     assert_trigger_error('request_method', array($env));
     assert_false(request_method());
   }

   
   function test_request_accepts()
   {
     $env = env();

     $env['SERVER']['HTTP_ACCEPT'] = null;
     assert_true(request_accepts('text/plain'));

     $env['SERVER']['HTTP_ACCEPT'] = 'text/html';
     assert_true(request_accepts('html'));

     $env['SERVER']['HTTP_ACCEPT'] = 'text/*; application/json';     
     assert_true(request_accepts('html'));
     assert_true(request_accepts('text/html'));
     assert_true(request_accepts('text/plain'));
     assert_true(request_accepts('application/json'));
     
     assert_false(request_accepts('image/png'));
     assert_false(request_accepts('png'));
     
     assert_true(defined('TESTS_DOC_ROOT'), "Undefined 'TESTS_DOC_ROOT' constant");
     
     $response =  test_request(TESTS_DOC_ROOT.'05-content_negociation.php', 'GET', false, array(), array("Accept: image/png"));
     assert_equal("Oops", $response);
     
     $response =  test_request(TESTS_DOC_ROOT.'05-content_negociation.php', 'GET', false, array(), array("Accept: text/html"));
     assert_equal("<h1>HTML</h1>", $response);
     
     $response =  test_request(TESTS_DOC_ROOT.'05-content_negociation.php', 'GET', false, array(), array("Accept: application/json"));
     assert_equal("json", $response);
   }
   
   
   function test_request_uri()
   {
     # TODO test with webbrick + CGIHandler (http://microjet.ath.cx/webrickguide/html/CGIHandler.html)
     # TODO request_uri must be also tested in a browser...
     
     assert_equal(request_uri(), "/");
     $path = dirname(__FILE__)."/helpers/show_request_uri.php";
     $cmd = "php -f $path";
     
     assert_equal(exec($cmd, $res), "/");

     assert_equal(exec($cmd." test", $res), "/test");
     
     assert_equal(exec($cmd." /test", $res), "/test");
     
     assert_equal(exec($cmd." /my-test/", $res), "/my-test");
     
     assert_not_equal(exec($cmd." /my-test/?", $res), "/my-test");
      
     assert_not_equal(exec($cmd." /my-test?var=1", $res), "/my-test");

   }
   
end_test_case();
   