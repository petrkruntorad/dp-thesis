import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import axios from 'axios';


/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        playlist: Object,
        currentMedia: Object,
        followingMedia: Object,
        playlistUrl: String,
        currentMediaShowToTimestamp: Number,
        followingMediaShowFromTimestamp: Number,
        timeToRefresh: Number,
        playlistChanged: String
    }

    connect() {
        this.keepPlaylistUpToDate();
    }

    async initialize() {
        //get component
        this.component = await getComponent(this.element);

        //init
        await this.loadPlaylist();

    }

    async loadPlaylist(refreshComponent = true, immediateRefresh = false) {
        //fetches playlist
        const response = await axios.get(this.playlistUrlValue)
        this.playlistValue = response.data;

        // checks if playlist is set
        if(this.playlistValue)
        {
            // checks if there is media that should be currently shown
            if(this.playlistValue.currentMedia)
            {
                // assigns current media to variable
                this.currentMediaValue = this.playlistValue.currentMedia;
                // assigns show to timestamp to variable
                this.currentMediaShowToTimestampValue = this.currentMediaValue.showToAsTimestamp;
                // calculates time to refresh
                this.timeToRefreshValue = this.currentMediaShowToTimestampValue - Date.now();
            }

            // checks if there is media that should be shown next
            if(this.playlistValue.followingMedia)
            {
                // assigns following media to variable
                this.followingMediaValue = this.playlistValue.followingMedia;
                // assigns show from timestamp to variable
                this.followingMediaShowFromTimestampValue = this.followingMediaValue.showFromAsTimestamp;

                // checks if there is no current media
                if(this.isObjectEmpty(this.currentMediaValue))
                {
                    // sets time to refresh to time of next media start
                    this.timeToRefreshValue = this.followingMediaShowFromTimestampValue - Date.now();
                }
            }

            if(this.playlistValue.lastUpdated)
            {
                // checks if playlistChangedValue is set
                if(!this.playlistChangedValue)
                {
                    // assigns last updated time of playlist to variable
                    this.playlistChangedValue = this.playlistValue.lastUpdated;
                }

                // assigns last updated time of playlist to variable
                let playListChanged = new Date(this.playlistValue.lastUpdated).getTime();
                let currentLastUpdated = new Date(this.playlistChangedValue).getTime();

                // checks if playlist has changed
                if(playListChanged > currentLastUpdated)
                {
                    // updates time of last change
                    this.playlistChangedValue = this.playlistValue.lastUpdated;

                    // sets immediate refresh to true
                    immediateRefresh = true;
                }
            }
        }

        //checks if playlist is set
        if(this.timeToRefreshValue <= 0)
        {
            // sets time to refresh to 60000ms to refresh every minute
            this.timeToRefreshValue = 60000;
        }

        //checks if immediate refresh is set
        if(immediateRefresh)
        {
            // sets time to refresh to 100ms to refresh immediately
            this.timeToRefreshValue = 100;
        }

        //checks if component should be refreshed
        if(refreshComponent)
        {
            //refreshes component
            await this.refreshCurrentMedia(this.timeToRefreshValue);
        }
    }

    async refreshCurrentMedia(timeToRefresh)
    {
        setTimeout( async () => {
            console.log('refreshing');
            //sets current media to null to prevent showing of old media
            this.component.set('currentMedia', null);
            //emits event to update media
            this.component.emit('media:update');
            //loads playlist again
            await this.loadPlaylist()
            //re-renders component
            this.component.render();
            console.log('refreshed');
        }, timeToRefresh);
    }

     async keepPlaylistUpToDate() {
        console.log('keepPlaylistUpToDate');
        setTimeout(async () => {
            await this.loadPlaylist();
            this.keepPlaylistUpToDate();
        }, 10000);
    }

    isObjectEmpty(obj) {
        //checks if object is empty
        return Object.keys(obj).length === 0;
    }
}
