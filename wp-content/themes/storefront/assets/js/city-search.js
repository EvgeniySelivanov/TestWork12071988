document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('city-search');
  const citiesTableBody = document.getElementById('cities-table').getElementsByTagName('tbody')[0];

  searchInput.addEventListener('input', function () {
      const searchTerm = this.value.trim();

      clearTimeout(window.searchTimer);
      window.searchTimer = setTimeout(function () {
          fetch(cityWeatherAjaxSearch.ajax_url, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: new URLSearchParams({
                  action: 'fetch_city_search_results',
                  search_term: searchTerm,
                  security: cityWeatherAjaxSearch.security, //Using a localized object for search
              }),
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  citiesTableBody.innerHTML = data.data.html;
              } else {
                  citiesTableBody.innerHTML = '<tr><td colspan="3">No results found.</td></tr>';
              }
          })
          .catch(error => {
              citiesTableBody.innerHTML = '<tr><td colspan="3">An error occurred.</td></tr>';
          });
      }, 300); // Search timeout
  });
});
