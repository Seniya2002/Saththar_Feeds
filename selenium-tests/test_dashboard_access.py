from selenium import webdriver
import time
from pathlib import Path

# Start browser
driver = webdriver.Chrome()

try:
    # Step 1: Directly access the protected page (index.php) without logging in
    driver.get("http://localhost/saththar_feeds/index.php")
    time.sleep(2)

    # Step 2: Verify redirection to login page
    current_url = driver.current_url

    if "login.php" in current_url:
        print("Dashboard Access Test: PASS  – Redirected to login.php")
        Path("selenium-tests/dashboard_access_test_result.txt").write_text("Dashboard Access Test: PASS  – Redirected to login.php", encoding="utf-8")
    else:
        print("Dashboard Access Test: FAIL  – User accessed index.php without authentication")
        Path("selenium-tests/dashboard_access_test_result.txt").write_text("Dashboard Access Test: FAIL  – User accessed index.php without authentication", encoding="utf-8")

finally:
    driver.quit()
