from selenium import webdriver
from selenium.webdriver.common.by import By
import time

try:
    driver = webdriver.Chrome()

    # Step 1: Go to login page
    driver.get("http://localhost/saththar_feeds/login.php")
    time.sleep(1)

    # Step 2: Login with test credentials
    driver.find_element(By.NAME, "email").send_keys("saramith@gmail.com")
    driver.find_element(By.NAME, "password").send_keys("1234")
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
    time.sleep(1)

    # Step 3: Logout (use your actual logout URL or button)
    driver.get("http://localhost/saththar_feeds/logout.php")
    time.sleep(1)

    # Step 4: Try accessing the protected page (e.g., index.php)
    driver.get("http://localhost/saththar_feeds/welcome.html")
    time.sleep(1)

    # Step 5: Check if redirected to login (i.e., session destroyed)
    if "login.php" in driver.current_url or "Login" in driver.page_source:
        print("Logout Test: PASS – Protected page redirected to login after logout.")
    else:
        print("Logout Test: FAIL  – Protected page accessible after logout.")

except Exception as e:
    print(f"Logout Test: FAIL  – Exception occurred: {str(e)}")

finally:
    driver.quit()
