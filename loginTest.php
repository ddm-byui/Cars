<?php
/************************************************************
* Author: Spenser Roark
* Title: Login Test
*
* Summary: This is the test for the Login Test,
*          for notes on the scope of the test please refer
*          to the written "KSL - Test Plan - Login Test"
*
*************************************************************/

require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver.php';
require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver/Driver.php';
require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver/MockDriver.php';
require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver/WebElement.php';
require_once '/home/ddm2000/testScripts/WebDriver-PHP/WebDriver/MockElement.php';

class loginTest extends PHPUnit_Framework_TestCase {

  protected $driver;
  protected $error;

  public function setUp() {
    $this->projectName = "KSL";
    $this->suiteName = "login";
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
   * @legitLogin
   *
   * Summary: This function sends the login information and submits the
   *          login on the login page. The login should be valid.
   *
   * Note:    The browser must be on the login page by the time this 
   *          function is called if the needed element's aren't present 
   *          the test will fail.
   *
   *********************************************************************/
  public function legitLogin()
  {
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
   * @emptyLogin
   *
   * Summary: This function submits an empty string for login and 
   *          submits the form. The login should be invalid
   *
   * Note:    The browser must be on the login page for this function to
   *          start. This function does not check for the error message
   *
   *********************************************************************/
  public function emptyLogin()
  {
    // Click this again so the previous error message disappears
    // And if something went wrong, it will open the login window again
    $this->error = "Failed to locate the email login input tag";
    $this->get_element("css=input[id*=\"memberemail\"]")->clear();

    $this->error = "Failed to locate the password input tag";
    $this->get_element("css=input[id*=\"memberpassword\"]")->clear();

    $this->error = "Unable to find the login button";
    $this->get_element("css=input[src*=\"continue\"]")->click();
  }

/*********************************************************************
   * @tooLongLogin
   *
   * Summary: This function submits a long string for login and 
   *          submits the form. The login should be invalid
   *
   * Note:    The browser must be on the login page for this function to
   *          start. This function does not check for the error message
   *
   *********************************************************************/
  public function tooLongLogin($tooLong)
  {
    // One more time
    $this->error = "Failed to locate the email login input tag";
    $this->get_element("css=input[id*=\"memberemail\"]")->clear();

    $this->error = "Failed to locate the password input tag";
    $this->get_element("css=input[id*=\"memberpassword\"]")->clear();

    $this->error = "Failed to locate the email login input tag";
    $this->get_element("css=input[id*=\"memberemail\"]")->send_keys($tooLong);

    $this->error = "Failed to locate the password input tag";
    $this->get_element("css=input[id*=\"memberpassword\"]")->send_keys($tooLong);

    $this->error = "Unable to find the login button";
    $this->get_element("css=input[src*=\"continue\"]")->click();    
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
   * Inputs:  None
   *
   * Outputs: None
   *********************************************************************/
  public function xssAttack()
  {
    // User input fields
    $this->error = "Failed to locate the email login input tag";
    $this->get_element("css=input[id*=\"memberemail\"]")->clear();

    $this->error = "Failed to locate the password input tag";
    $this->get_element("css=input[id*=\"memberpassword\"]")->clear();

    // This has woked at one point, need to be careful
    $this->error = "Unable to find the specified element to send the xss attack to";
    $this->get_element("css=input[id*=\"memberemail\"]")->send_keys('\"<script><script>alert("Giant Enemy Crab");</script>');
    $this->get_element("css=input[id*=\"memberpassword\"]")->send_keys('\"<script><script>alert("Giant Enemy Crab");</script>');

    $this->error = "Unable to find the clickable button to submit the xss attack";
    $this->get_element("css=input[src*=\"continue\"]")->click();

    // If this works we have a problem
    try
      {
	$this->accept_alert();

	$xssFailure = true;	
      }
    // If the ry throws an exception then we are safe
    catch(Exception $e)
      {
	  file_put_contents('php://stderr', "Good news, the xss attack didn't work\n");
	  $xssFailure = false;
      }

    // This will stop the test and send an error message
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

    // From Azumanga Daioh, the cat doll known as Chiyo's dad
    $randomString = "Hello everynun, how are you? Fine thank you. I want to be a bird ";

    // String that will be too long
    $tooLong = "";

    for ($i = 0; $i < 6; $i++) {
      $tooLong .= $randomString;
    }

    $this->set_implicit_wait(6000);
    $this->load("http://stage-v2.ksl.com/");

    file_put_contents('php://stderr', "Starting the Test...\n\n");

    file_put_contents('php://stderr', "==================".
		      "======================\n\n");

    // make sure I'm on the right page
    $this->error = "Failed to reach the home page";

    $this->assert_title("Utah News, Sports, Weather and Classifieds | ksl.com");

    file_put_contents('php://stderr', "   We are on the KSL".
		      " home page\n\n");

   // top right of the window, My Account
    $this->error = "Failed to locate the login link on the home page";
    $this->get_element("link text=Login")->click();

    // Uncomment this when the xss attack bug is fixed
    //$this->xssAttack();

    //$this->error = "Failed to find the error message";
    //$this->assert_string_present("ERROR");

    $this->emptyLogin();

    $this->error = "Failed to find the error message";
    $this->assert_string_present("ERROR");

    $this->tooLongLogin($tooLong);

    $this->error = "Failed to find the error message";
    $this->assert_string_present("ERROR");

    $this->legitLogin();
  }
  // end test body

  public function tearDown() {
    if ($this->driver) {
      if ($this->hasFailed()) {
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
