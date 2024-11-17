document.addEventListener('DOMContentLoaded', function () {
    const citySelector = document.getElementById('city-selector');
    const weatherOutput = document.getElementById('weather-output');
  
    if (citySelector && weatherOutput && window.cityWeatherAjax) {
      citySelector.addEventListener('change', function () {
        const cityId = this.value;
  
        if (cityId) {
          fetch(cityWeatherAjax.ajax_url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              action: 'fetch_city_weather',
              city_id: cityId,
              security: cityWeatherAjax.security,//Using a localized object for search
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                weatherOutput.innerHTML = `<p>Temperature: ${data.data.temperature}°C</p>`;
              } else {
                weatherOutput.innerHTML = `<p>${data.data}</p>`;
              }
            })
            .catch((error) => {
              weatherOutput.innerHTML = `<p>Unable to fetch weather data.</p>`;
              console.error(error);
            });
        } else {
          weatherOutput.innerHTML = '';
        }
      });
    }
  
  
  });
  