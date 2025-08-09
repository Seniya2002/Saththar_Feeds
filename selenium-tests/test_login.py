from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time


# === Test Data ===
email = "test@gmail.com"
password = "1234"

# === Test Result File Path ===
result_file = "selenium-tests/test_result.txt"

# === Start WebDriver ===
driver = webdriver.Chrome()

try:
    # === Step 1: Open the login page ===
    driver.get("http://localhost/saththar_feeds/login.php")

    # === Step 2: Wait for the email field ===
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.NAME, "email")))

    # === Step 3: Fill in login form ===
    driver.find_element(By.NAME, "email").send_keys(email)
    driver.find_element(By.NAME, "password").send_keys(password)

    # === Step 4: Submit form ===
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()

    # === Step 5: Wait for redirect to index.php ===
    WebDriverWait(driver, 5).until(EC.url_contains("index"))

    # === Step 6: Record PASS ===
    result = "PASS"
    print("Login test passed.")

except Exception as e:
    # === On error, record FAIL ===
    result = f"FAIL: {str(e)}"
    print(" Login test failed:", e)

finally:
    # === Step 7: Save result to text file ===
    with open(result_file, "w") as f:
        f.write(f"Login Test Result: {result}\n")

    # === Step 8: Pause before closing (for visibility) ===
    time.sleep(5)
    driver.quit()
