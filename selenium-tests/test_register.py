from selenium import webdriver
from selenium.webdriver.common.by import By
import time
from pathlib import Path

try:
    # Set up Chrome WebDriver
    driver = webdriver.Chrome()

    # Open the registration page
    driver.get("http://localhost/saththar_feeds/register.php")
    time.sleep(10)

    # Fill out the form fields
    driver.find_element(By.NAME, "name").send_keys("TestUser")
    driver.find_element(By.NAME, "email").send_keys("testuser@gmail.com")
    driver.find_element(By.NAME, "password").send_keys("password123")
    driver.find_element(By.NAME, "confirm_password").send_keys("password123")

    # Submit the form
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
    time.sleep(6)

    # Check if redirected to login or success message shown
    success = "login.php" in driver.current_url or "Registration Successful" in driver.page_source

    # Save result
    output_file = Path("selenium-tests/register_test_result.txt")
    if success:
        output_file.write_text("Registration Test: PASS", encoding="utf-8")
        print("Registration Test: PASS")
    else:
        output_file.write_text("Registration Test: FAIL – Registration may have failed or no redirect occurred.", encoding="utf-8")
        print("Registration Test: FAIL – Registration may have failed or no redirect occurred.")

except Exception as e:
    error_message = f"Registration Test: FAIL – Exception occurred: {str(e)}"
    Path("selenium-tests/register_test_result.txt").write_text(error_message, encoding="utf-8")
    print(error_message)

finally:
    time.sleep(5)
    driver.quit()
