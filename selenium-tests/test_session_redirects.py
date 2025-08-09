from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
from pathlib import Path

# Set up Chrome WebDriver
options = webdriver.ChromeOptions()
options.add_argument('--start-maximized')
driver = webdriver.Chrome(options=options)

try:
    wait = WebDriverWait(driver, 10)

    # Step 1: Open login page
    driver.get("http://localhost/saththar_feeds/login.php")

    # Step 2: Log in with valid credentials
    wait.until(EC.presence_of_element_located((By.NAME, "email"))).send_keys("saramith@gmail.com")
    wait.until(EC.presence_of_element_located((By.NAME, "password"))).send_keys("1234")
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()

    time.sleep(2)

    # Step 3: Try to access login page again while logged in
    driver.get("http://localhost/saththar_feeds/login.php")
    time.sleep(2)
    current_url = driver.current_url

    if "index.php" in current_url:
        print("Session Redirect Test (Login Page): PASS – Redirected to index.php when logged in")
    else:
        print("Session Redirect Test (Login Page): FAIL – Still on login.php despite being logged in")

    # Step 4: Logout (simulate logout manually or add logout code if you have a logout URL)
    driver.get("http://localhost/saththar_feeds/logout.php")  # This file must destroy session
    time.sleep(2)

    # Step 5: Try accessing index.php again after logout
    driver.get("http://localhost/saththar_feeds/index.php")
    time.sleep(2)

    if "login.php" in driver.current_url:
        result = "Session Redirect Test (After Logout): PASS – Redirected to login.php"
    else:
        result = "Session Redirect Test (After Logout): FAIL – Still accessed index.php after logout"

    print(result)
    Path("selenium-tests/session_redirect_test_result.txt").write_text(result, encoding="utf-8")

except Exception as e:
    error_message = f"Session Redirect Test: FAIL – Exception occurred: {str(e)}"
    print(error_message)
    Path("selenium-tests/session_redirect_test_result.txt").write_text(error_message, encoding="utf-8")

finally:
    driver.quit()
