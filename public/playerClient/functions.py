# imports
import os
import requests
import socket
import json
from crontab import CronTab
from selenium import webdriver
from selenium.webdriver.chrome.service import Service as ChromeService
import psutil
from contextlib import suppress

# variables
# cron jobs identifies
base_cron_job_identifier = 'kioskMediaPlayer'
update_config_cron_job_identifier = base_cron_job_identifier + '-update-config'
check_player_cron_job_identifier = base_cron_job_identifier + '-check-player'
restart_player_cron_job_identifier = base_cron_job_identifier + '-restart-player'
update_device_details_cron_job_identifier = base_cron_job_identifier + '-update-device-details'
start_player_cron_job_identifier = base_cron_job_identifier + '-start-player'
init_player_after_start_cron_job_identifier = base_cron_job_identifier + '-init-player'
# directories paths
root_directory: str = os.path.dirname(os.path.realpath(__file__))
temp_directory: str = root_directory + "/temp/"
config_directory: str = root_directory + "/config/"
# file paths
browser_temp_file: str = temp_directory + "browser_temp.json"
config_file: str = config_directory + "config.json"

cron = CronTab(user=True)


# checks if cron job is already created
def is_cron_job_set(cron_comment: str):
    # iterates cron jobs
    for job in cron:
        # checks if cron job with specified comment exists
        if job.comment == cron_comment:
            return True
    return False


# creates new cron job
def define_cron_job(interval: str, cron_comment: str, command: str):
    # checks if cronjob exists
    if is_cron_job_set(cron_comment):
        # updates cronjob
        update_cron_job(interval=interval, cron_comment=cron_comment)
    else:
        # creates new cronjob for main script
        job = cron.new(command=command, comment=cron_comment)
        job.setall(interval)
        cron.write()


# updates existing cron job
def update_cron_job(interval: str, cron_comment: str):
    # looks for cronjob with specified comment
    for job in cron:
        if job.comment == cron_comment:
            job.setall(interval)
        cron.write()


# gets IP address of device
def get_current_device_ip():
    # sets socket
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    # connects to 8.8.8.8 to get local IP address
    s.connect(("8.8.8.8", 80))
    # returns local IP address
    return socket.gethostbyname(s.getsockname()[0])


# starts browser with specified url
def start_browser(url: str):
    options = webdriver.ChromeOptions()
    # sets chrome to maximized mode on start
    options.add_argument("--start-maximized")
    options.add_experimental_option('excludeSwitches', ['enable-automation'])
    # starts chrome in kiosk mode
    options.add_argument("--kiosk")
    # allows to use autoplay with video that uses sound
    options.add_argument('--autoplay-policy=no-user-gesture-required')
    # starts chrome as separate process otherwise chrom will close when script finished
    options.add_experimental_option("detach", True)

    # sets path for chrome driver on raspbian
    chrome_service = ChromeService(executable_path='/usr/lib/chromium-browser/chromedriver')
    driver = webdriver.Chrome(service=chrome_service, options=options)

    # starts browser at specific url
    driver.get(url)

    process_pids = []
    # possible names of used browser
    valid_process_names = ['chromium-browser']

    # iterates through processes and looks for processes with required name and parameters
    for process in psutil.process_iter():
        if process.name() in valid_process_names and '--test-type=webdriver' in process.cmdline():
            with suppress(psutil.NoSuchProcess):
                process_pids.append(process.pid)

    # check if there are process pids and saves them to temp json file
    if len(process_pids) > 0:
        # saves browser pids to temp file
        store_browser_process_id(process_pids)


# saves process pid to temp file
def store_browser_process_id(process_ids: list):
    # create temp dir if not exists
    create_temp_dir()

    # Data to be written
    data = {
        "process_ids": process_ids,
    }

    # writes data to file
    with open(browser_temp_file, 'w') as file:
        json.dump(data, file)


# gets browser pid from temp file
def load_browser_process_id():
    process_id: None | list = None
    if os.path.exists(browser_temp_file):
        # Opening JSON file
        temp_file = open(browser_temp_file)

        # returns JSON object as
        # a dictionary
        data = json.load(temp_file)

        # Closing file
        temp_file.close()

        # assigns process id to var
        if 'process_ids' in data.keys():
            process_id = data["process_ids"]

    # return process id or none
    return process_id


# loads config as dictionary
def get_config_as_dictionary():
    config: None | dict = None
    if os.path.exists(config_file):
        # Opening JSON file
        config_data = open(config_file)

        # returns JSON object as
        # a dictionary
        data = json.load(config_data)

        # Closing file
        config_data.close()

        # assigns process id to var
        if data:
            config = data

    # return config
    return config


# creates temp directory
def create_temp_dir():
    # check if temp directory not exists
    if not os.path.exists(temp_directory):
        # creates temp directory
        os.makedirs(temp_directory)


# checks if player browser is running
def is_browser_running():
    # inits vars
    is_running: bool = False
    # gets process ids from browser temp file
    process_ids = load_browser_process_id()

    # check if there are available process ids
    if process_ids is not None:
        # iterates through ids and checks if there are processes with specified pids
        for process_id in process_ids:
            if psutil.pid_exists(process_id):
                is_running = True

    return is_running


# starts browser process if browser is not running
def start_browser_process():
    # loads config
    config: None | dict = get_config_as_dictionary()
    # check if browser is not running
    if not is_browser_running():
        # checks if config is set
        if config is not None:
            # checks if player URL is set in config
            if 'playerUrl' in config.keys():
                # starts browser at specified URL
                start_browser(config['playerUrl'])


# kills existing browser processes
def kill_browser_process():
    # loads browser pids
    process_pids: None | list = load_browser_process_id()
    # checks if process pids are defined
    if process_pids is not None:
        # iterates through process pids
        for process_id in process_pids:
            # checks if pids exists
            if psutil.pid_exists(process_id):
                # kills process for specified pid
                process = psutil.Process(process_id)
                process.terminate()


# function for killing of active player processes and creating new one
def restart_browser_process():
    # kills browser processes
    kill_browser_process()
    # starts new browser process
    start_browser_process()


# function for obtaining of device configuration from server
def get_config_from_server():
    get_config_url: str | None = None
    # gets config
    config: None | dict = get_config_as_dictionary()
    # check if config is set
    if config is not None:
        # check if requested parameter is in config
        if 'getConfigUrl' in config.keys():
            get_config_url = config['getConfigUrl']
    # checks if get config url is available
    if get_config_url is not None:
        # sends request for current config
        response = requests.post(get_config_url, verify=False)
        # checks if server responded successfully
        if response.status_code == 200:
            # gets content
            data = response.content
            # saves data to config file
            with open(config_file, 'wb') as s:
                s.write(data)


# gets disk usage details
def get_disk_usage():
    return psutil.disk_usage(root_directory)


# function for updating of device details on server
def update_device_details():
    update_device_details_url: str | None = None
    # gets config
    config: None | dict = get_config_as_dictionary()
    # check if config is set
    if config is not None:
        # check if requested parameter is in config
        if 'updateDeviceDetailsUrl' in config.keys():
            update_device_details_url = config['updateDeviceDetailsUrl']

    if update_device_details_url is not None:
        session = requests.Session()
        # prepares data
        data = {'ipAddress': get_current_device_ip(), 'diskUsage': get_disk_usage().percent,
                'diskCapacity': get_disk_usage().total}
        # sends data to endpoint
        insert_request = session.post(url=update_device_details_url, data=data, verify=False)
        # prints response
        print(insert_request.text)


# gets command for refreshing browser
def get_refresh_browser_command():
    return 'python ' + root_directory + '/refresh_browser.py'


# gets command for updating of config
def get_update_config_command():
    return 'python ' + root_directory + '/update_config.py'


# gets command for initialization of player
def get_init_player_command():
    return 'python ' + root_directory + '/init_player.py'


# gets command for initialization of device
def get_init_device_command():
    return 'python ' + root_directory + '/init_device.py'


# gets command for checking if browser is running and if not starts new process
def get_check_if_browser_is_running_command():
    return 'python ' + root_directory + '/check_player_status.py'


# gets command for updating of device details
def get_update_device_details_command():
    return 'python ' + root_directory + '/update_device_details.py'


# gets command for initialization of player after 60 seconds after boot
def init_player_after_start():
    return 'sleep 60 && python ' + root_directory + '/init_player.py'


def init_cron():
    # gets config
    config: None | dict = get_config_as_dictionary()

    # checks if config is defined
    if config is not None:
        # check if cron job with specified identifier is not set and then creates it otherwise updates existing one
        if not is_cron_job_set(update_config_cron_job_identifier):
            define_cron_job(config['configUpdateTimeCron'], update_config_cron_job_identifier,
                            get_update_config_command())
        else:
            update_cron_job(config['configUpdateTimeCron'], update_config_cron_job_identifier,
                            get_update_config_command())

        # check if cron job with specified identifier is not set and then creates it otherwise updates existing one
        if not is_cron_job_set(check_player_cron_job_identifier):
            define_cron_job(config['checkIfPlayerIsRunningTimeCron'], check_player_cron_job_identifier,
                            get_check_if_browser_is_running_command())
        else:
            update_cron_job(config['checkIfPlayerIsRunningTimeCron'], check_player_cron_job_identifier,
                            get_check_if_browser_is_running_command())

        # check if cron job with specified identifier is not set and then creates it otherwise updates existing one
        if not is_cron_job_set(restart_player_cron_job_identifier):
            define_cron_job(config['restartPlayerTimeCron'], restart_player_cron_job_identifier,
                            get_refresh_browser_command())
        else:
            update_cron_job(config['restartPlayerTimeCron'], restart_player_cron_job_identifier,
                            get_refresh_browser_command())

        # check if cron job with specified identifier is not set and then creates it otherwise updates existing one
        if not is_cron_job_set(update_device_details_cron_job_identifier):
            define_cron_job(config['updateDeviceDetails'],
                            update_device_details_cron_job_identifier, get_update_device_details_command())
        else:
            update_cron_job(config['updateDeviceDetails'],
                            update_device_details_cron_job_identifier, get_update_device_details_command())

        # check if cron job with specified identifier is not set and then creates it otherwise updates existing one
        if not is_cron_job_set(init_player_after_start_cron_job_identifier):
            define_cron_job(config['startPlayer'],
                            init_player_after_start_cron_job_identifier, init_player_after_start())
        else:
            update_cron_job(config['startPlayer'],
                            init_player_after_start_cron_job_identifier, init_player_after_start())
    else:
        print('Config not defined.')
