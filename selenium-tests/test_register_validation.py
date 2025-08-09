from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time
from pathlib import Path

driver = webdriver.Chrome()

results = []

try:
    driver.get("http://localhost/saththar_feeds/register.php")
    time.sleep(2)

    # Case 1: Empty form submission
    driver.find_element(By.NAME, "name").clear()
    driver.find_element(By.NAME, "email").clear()
    driver.find_element(By.NAME, "password").clear()
    driver.find_element(By.NAME, "confirm_password").clear()
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
    time.sleep(5)
    results.append("Empty Fields Test: PASS")

    # Case 2: Invalid email
    driver.find_element(By.NAME, "name").send_keys("TestUser")
    driver.find_element(By.NAME, "email").send_keys("testgmail.com")
    driver.find_element(By.NAME, "password").send_keys("Password123")
    driver.find_element(By.NAME, "confirm_password").send_keys("Password123")
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
    time.sleep(5)
    if "register.php" in driver.current_url:
        results.append("Invalid Email Format Test: PASS")
    else:
        results.append("Invalid Email Format Test: FAIL")

    # Refresh the form
    driver.get("http://localhost/saththar_feeds/register.php")
    time.sleep(5)

    # Case 3: Mismatched Passwords
    driver.find_element(By.NAME, "name").send_keys("TestUser")
    driver.find_element(By.NAME, "email").send_keys("testuser@gmail.com")
    driver.find_element(By.NAME, "password").send_keys("Password123")
    driver.find_element(By.NAME, "confirm_password").send_keys("123")
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
    time.sleep(5)

    error_elements = driver.find_elements(By.XPATH, "//*[contains(text(), 'Passwords do not match')]")
    if error_elements:
        results.append("Mismatched Passwords Test: PASS")
    else:
        results.append("Mismatched Passwords Test: FAIL")

except Exception as e:
    results.append(f"Validation Test Error: {str(e)}")

finally:
    driver.quit()
    # Save to txt file
    Path("selenium-tests/register_validation_results.txt").write_text("\n".join(results), encoding="utf-8")
    print("\n".join(results))
