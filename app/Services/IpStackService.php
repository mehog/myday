<?php

namespace App\Services;

class IpStackService
{
    public $ip_stack_data;

    public function __construct($ip_stack_data)
    {
        $this->ip_stack_data = $ip_stack_data;
    }

    public function getTimeZone()
    {
        return isset($this->ip_stack_data->time_zone) ? $this->ip_stack_data->time_zone : null;
    }

    public function getCountry()
    {
        return isset($this->ip_stack_data->country_name) ? $this->ip_stack_data->country_name : null;
    }

    public function getCity()
    {
        return isset($this->ip_stack_data->city) ? $this->ip_stack_data->city : null;
    }

    public function getRegion()
    {
        return isset($this->ip_stack_data->region_name) ? $this->ip_stack_data->region_name : null;
    }

    public function getPostalCode()
    {
        return isset($this->ip_stack_data->zip) ? $this->ip_stack_data->zip : null;
    }

    public function getLatitude()
    {
        return isset($this->ip_stack_data->latitude) ? $this->ip_stack_data->latitude : null;
    }

    public function getLongitude()
    {
        return isset($this->ip_stack_data->longitude) ? $this->ip_stack_data->longitude : null;
    }

    public function getCurrency()
    {
        return isset($this->ip_stack_data->currency) ? $this->ip_stack_data->currency : null;
    }

    public function getLanguage()
    {
        return isset($this->ip_stack_data->location->languages[0]->name) ? $this->ip_stack_data->location->languages[0]->name : null;
    }

    public function getCountryFlag()
    {
        return isset($this->ip_stack_data->location->country_flag) ? $this->ip_stack_data->location->country_flag : null;
    }

    public function getCountryFlagEmoji()
    {
        return isset($this->ip_stack_data->location->country_flag_emoji) ? $this->ip_stack_data->location->country_flag_emoji : null;
    }

    public function getCountryFlagEmojiUnicode()
    {
        return isset($this->ip_stack_data->location->country_flag_emoji_unicode) ? $this->ip_stack_data->location->country_flag_emoji_unicode : null;
    }

    public function getCapital()
    {
        return isset($this->ip_stack_data->location->capital) ? $this->ip_stack_data->location->capital : null;
    }

    public function getCallingCode()
    {
        return isset($this->ip_stack_data->location->calling_code) ? $this->ip_stack_data->location->calling_code : null;
    }

    public function getIsEu()
    {
        return isset($this->ip_stack_data->location->is_eu) ? $this->ip_stack_data->location->is_eu : null;
    }

    public function getGeonameId()
    {
        return isset($this->ip_stack_data->location->geoname_id) ? $this->ip_stack_data->location->geoname_id : null;
    }

    public function getAsn()
    {
        return isset($this->ip_stack_data->connection->asn) ? $this->ip_stack_data->connection->asn : null;
    }

    public function getIsp()
    {
        return isset($this->ip_stack_data->connection->isp) ? $this->ip_stack_data->connection->isp : null;
    }

    public function getSld()
    {
        return isset($this->ip_stack_data->connection->sld) ? $this->ip_stack_data->connection->sld : null;
    }

    public function getTld()
    {
        return isset($this->ip_stack_data->connection->tld) ? $this->ip_stack_data->connection->tld : null;
    }

    public function getHome()
    {
        return isset($this->ip_stack_data->connection->home) ? $this->ip_stack_data->connection->home : null;
    }

    public function getCarrier()
    {
        return isset($this->ip_stack_data->connection->carrier) ? $this->ip_stack_data->connection->carrier : null;
    }

    public function getIsicCode()
    {
        return isset($this->ip_stack_data->connection->isic_code) ? $this->ip_stack_data->connection->isic_code : null;
    }

    public function getNaicsCode()
    {
        return isset($this->ip_stack_data->connection->naics_code) ? $this->ip_stack_data->connection->naics_code : null;
    }

    public function getOrganizationType()
    {
        return isset($this->ip_stack_data->connection->organization_type) ? $this->ip_stack_data->connection->organization_type : null;
    }

    public function getRegionCode()
    {
        return isset($this->ip_stack_data->region_code) ? $this->ip_stack_data->region_code : null;
    }

    public function getCountryCode()
    {
        return isset($this->ip_stack_data->country_code) ? $this->ip_stack_data->country_code : null;
    }

    public function getContinentCode()
    {
        return isset($this->ip_stack_data->continent_code) ? $this->ip_stack_data->continent_code : null;
    }

    public function getContinentName()
    {
        return isset($this->ip_stack_data->continent_name) ? $this->ip_stack_data->continent_name : null;
    }

    public function getConnectionType()
    {
        return isset($this->ip_stack_data->connection_type) ? $this->ip_stack_data->connection_type : null;
    }

    public function getIpRoutingType()
    {
        return isset($this->ip_stack_data->ip_routing_type) ? $this->ip_stack_data->ip_routing_type : null;
    }

    public function getDma()
    {
        return isset($this->ip_stack_data->dma) ? $this->ip_stack_data->dma : null;
    }

    public function getMsa()
    {
        return isset($this->ip_stack_data->msa) ? $this->ip_stack_data->msa : null;
    }

    public function getIpType()
    {
        return isset($this->ip_stack_data->type) ? $this->ip_stack_data->type : null;
    }

    public function getIpAddress()
    {
        return isset($this->ip_stack_data->ip) ? $this->ip_stack_data->ip : null;
    }
}
