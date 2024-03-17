import functions
from selenium import webdriver
from selenium.webdriver.chrome.service import Service as ChromeService
from webdriver_manager.chrome import ChromeDriverManager
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.remote.command import Command
import psutil
from contextlib import suppress

#functions.start_browser('https://localhost:8000/player/8f5d72dcb48090daf9d002b75d6704f7')

#print(functions.is_browser_running())

#functions.store_browser_process_id('chromedriver')

#for process in psutil.process_iter():
#    print(process)

#print(functions.get_config_as_dictionary())

#functions.start_browser_process()

#functions.kill_browser_process()

print(functions.start_browser_process())