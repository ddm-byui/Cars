<?php
/************************************************************
* Author: Spenser Roark
* Title: Search For Car Test
*
* Summary: This is the test for the Searh For a Car Test,
*          for notes on the scope of the test please refer
*          to the written "KSL - Test Plan - Car Search Test"
*
*************************************************************/

require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver.php';
require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver/Driver.php';
require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver/MockDriver.php';
require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver/WebElement.php';
require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver/MockElement.php';

class searchForCar extends PHPUnit_Framework_TestCase {

  protected $driver;
  protected $error;

  public function setUp() {
    $this->projectName = "KSL";
    $this->suiteName = "searchForCar";
    $this->fileName = substr(strrchr(__FILE__, "/"), 1);
    $this->browser = "firefox";
    $this->driver = WebDriver_Driver::InitAtHost("172.16.98.129",
						 "4444", "firefox", array('version'=>'spenser'));
  }

  // Forward calls to main driver
  public function __call($name, $arguments) {
    if (method_exists($this->driver, $name)) {
      return call_user_func_array(
				  array($this->driver, $name),
				  $arguments);
    } else {
      throw new Exception("Tried to call nonexistent".
			  " method $name with arguments:\n" .
			  print_r($arguments, true));
    }
  }

  /*********************************************************************
   * @login
   *
   * Summary: This function sends the login information and submits the
   *          login on the login page. The login should be valid.
   *
   * Note:    The browser must be on the home page by the time this
   *          function is called if the needed element's aren't present
   *          the test will fail.
   *
   *********************************************************************/
  public function login()
  {
    // top right of the window, My Account
    $this->error = "Failed to locate the login link on the home page";
    $this->get_element("link text=Login")->click();

    // Clear in case there is leftover text from the other testing
    $this->error = "Failed to locate the email login input tag";
    $this->get_element("css=input[id*=\"memberemail\"]")->clear();

    $this->error = "Failed to locate the password input tag";
    $this->get_element("css=input[id*=\"memberpassword\"]")->clear();

    // this login and pass should work
    $this->error = "Failed to locate the email login input tag";
    $this->get_element("css=input[id*=\"memberemail\"]")->send_keys("ddm.byui.test@gmail.com");

    $this->error = "Failed to locate the password login input tag";
    $this->get_element("css=input[id*=\"memberpassword\"]")->send_keys("ddm2000");

    $this->error = "Unable to find the login button";
    $this->get_element("css=input[src*=\"continue\"]")->click();

    file_put_contents('php://stderr', "   Login was successful\n\n");
  }

  /*********************************************************************
   * @xssAttack
   *
   * Summary: This function inserts the injection attack text into an
   *          input field and checks whether it works or not. If the
   *          attack works the test is exited and the program teardown
   *          is executed like normal. If it does not work the test
   *          proceeds like normal
   *
   * Note:    It is desirable that the test fails, we don't want
   *          xss attacks to work.
   *
   * Inputs:  attackThis - css reference that the user wants to send the
   *                        attack to.
   *             E.g. some input field that accepts text would work best
   *
   *          howToSubmit - css reference that must be clicked in order
   *                        for the xss text to be sent to the server
   *                        and evaluated
   *
   * Outputs: None
   *********************************************************************/
  public function xssAttack($attackThis, $howToSubmit)
  {
    $this->error = "Unable to find the specified element to send the xss attack to";
    $this->get_element($attackThis)->send_keys('\"<script><script>alert("OOUG");</script>');

    $this->error = "Unable to find the clickable button to submit the xss attack";
    $this->get_element($howToSubmit)->click();

    try
      {
	$this->accept_alert();

	$xssFailure = true;
      }
    catch(Exception $e)
      {
	  file_put_contents('php://stderr', "Good news, the xss attack didn't work\n");
	  $xssFailure = false;
      }

    if($xssFailure)
      {
	$this->error = "XSS INJECTION ATTACK WORKED";

	$this->fail("xss attack was successful, please fix this as soon as possible\n\n");
      }
  }

    /*********************************************************************
   * @test
   *
   * Summary: This is the main testing function
   *
   * Inputs:  None
   *
   * Outputs: None
   *********************************************************************/
  public function test() {

    $this->error = "Failed before the test started\n";

    // Winning text in the NES Ghostbusters game
    $randomString = "Conglaturation !!! You have completed a great game. ";

    // String that will be too long
    $tooLong = "";

    for ($i = 0; $i < 8; $i++) {
      $tooLong .= $randomString;
    }

    $this->set_implicit_wait(15000);
    $this->load("http://stage-v2.ksl.com");

    file_put_contents('php://stderr', "Starting the Test...\n\n");

    file_put_contents('php://stderr', "==================".
		      "======================\n\n");

    // make sure I'm on the right page
    $this->error = "Failed to reach the home page";

    $this->assert_title("Utah News, Sports, Weather and Classifieds | ksl.com");

    file_put_contents('php://stderr', "   We are on the KSL".
		      " home page\n\n");

    $this->error = "Failed to reach the initial auto search page";
    $this->load("http://stage-v2.ksl.com/auto");

    // Beginning of the main search page tests
    // Searching wih an empty string
    $this->error = "Failed to click on the search button after submitting an empty test";
    $this->get_element("css=input[class*=\"searchButton\"]")->click();

    $this->error = "Failed to determine if we reached the search page for the empty string test";
    $this->assert_title("Autos Search | ksl.com");

    $this->error = "Couldn't go back to the search page during the empty string test";
    $this->go_back();


    // Searching with a long string
    $this->error = "Failed to find the keyword text field for the lengthy string test";
    $this->get_element("css=input[id*=\"searchFormKeyword\"]")->send_keys($tooLong);

    $this->error = "Failed to find the zip code text field for the lengthy string test";
    $this->get_element("css=input[id*=\"searchFormZipCode\"]")->send_keys($tooLong);

    $this->error = "Failed to find the search button for the lengthy string test";
    $this->get_element("css=input[class*=\"searchButton\"]")->click();

    $this->error = "Failed to determine if we reached the search page for the lengthy string test";
    $this->assert_title("Autos Search | ksl.com");

    $this->error = "Couldn't go back to the search page during the lengthy string test";
    $this->go_back();

    // legit search area start
    $this->error = "Failed to find the keyword text field for the legitimate string test";
    $this->get_element("css=input[id*=\"searchFormKeyword\"]")->send_keys("a");

    $this->error = "Failed to find the zip code text field for the legitimate string test";
    $this->get_element("css=input[class*=\"searchButton\"]")->click();

    $this->error = "Failed to determine if we reached the search page for the legitimate string test";
    $this->assert_title("Autos Search | ksl.com");

    $this->error = "Failed to find the \"Shop All Makes\" link on the search results page";
    $this->get_element("css=a[id*=\"shop_all_makes_link\"]")->click();

    $this->error = "Failed to close the \"Shop All Makes\" window on the search results page";
    $this->get_element("css=div[id*=\"cboxClose\"]")->click();

    $this->error = "Failed to find the [x] clear link in the search box on the search results page";
    $this->get_element("css=span[id*=\"keywordInputClearIcon\"]")->click();

    // The page reloads after clearing the search,
    // this gives time for that to happen

    // Beginning of search results page tests
    exec('sleep 5');

    $this->error = "Failed to find the most popular links on the search results page";
    $this->assert_element_present("css=.mostpopularlist");

    $this->error = "Something went wrong with the ad";
    // In case the ad isn't there
    try
      {
	$this->assert_element_present("css=#featured-ad");
      }
    catch(Exception $e)
      {
	file_put_contents('php://stderr', "   No ad was present on the search results page");
	$this->error = "Failed to find the featured ad on the search results page";
      }



    $this->error = "Failed to find the search results on the search results page";
    $this->assert_element_present("css=.srp-listing-body");

    // Search box on results page
    // Searching wih an empty string
    $this->error = "Failed to find the submit button on the search results page";
    $this->get_element("css=input[class*=\"searchSubmit\"]")->click();

    $this->error = "Failed to determine if we reached the search page for the second empty string test";
    $this->assert_title("Autos Search | ksl.com");

    $this->error = "Failed to go back to the first search results page from the second empty string test";
    $this->go_back();


    // Searching with a long string
    $this->error = "Failed to find the keyword text field for the second lengthy string test";
    $this->get_element("css=input[id*=\"keywordInput\"]")->send_keys($tooLong);

    $this->error = "Failed to find the submit button on the search results page for the second lengthy string test";
    $this->get_element("css=input[class*=\"searchSubmit\"]")->click();

    $this->error = "Failed to determine if we reached the search page for the second lengthy string test";
    $this->assert_title("Autos Search | ksl.com");

    $this->error = "Failed to go back to the first search results page from the second lengthy string test";
    $this->go_back();
  }
  // end test body

  public function tearDown() {
    if ($this->driver) {
      if ($this->hasFailed()) {

	$this->error .= "\nError Location Address: " . $this->get_url() . "\n";

	$error = "\t========================================\n" .
                 "\t= Search For Car Test Error            =\n" .
                 "\t========================================\n\n" .
	         "\t$this->error\n\n";

	$fileName = "/home/ddm2000/testScripts/ksl/mail/stats";
	$fout = fopen($fileName, 'a') or die("File did not open");
	fwrite($fout, "" . $error . "\n\n");

	file_put_contents('php://stderr', $error);

	fclose($fin);

	// This might be useless
	$this->driver->set_sauce_context("passed", false);
      } else {
	$this->driver->set_sauce_context("passed", true);
      }
      $this->driver->quit();
    }
    parent::tearDown();
  }
}
?>
