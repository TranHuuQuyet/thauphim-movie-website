document.addEventListener("DOMContentLoaded", () => {
    const API_BASE = "/api";
    const movieTitle = document.querySelector("#genre-movies-title");
    const movieStatus = document.querySelector("#genreMovieStatus");
    const movieList = document.querySelector("#genreMovieList");
  
    if (!movieTitle || !movieStatus || !movieList) return;
  
    const createMovieCard = (movie) => {
      const card = document.createElement("a");
      card.href = `/pages/movie-detail.php?id=${movie.id}`; 
      card.className = "movie-item-card"; 
  
      const posterWrap = document.createElement("div");
      posterWrap.className = "poster-wrapper";
  
      const img = document.createElement("img");
      img.src = movie.poster || "/assets/images/no-poster.png";
      img.alt = movie.title || "Movie Poster";
      img.loading = "lazy";
      posterWrap.append(img);
  
      const title = document.createElement("h3");
      title.className = "movie-item-title";
      title.textContent = movie.title;
  
      card.append(posterWrap, title);
      return card;
    };
  
    const loadGenreMovies = async () => {
      const urlParams = new URLSearchParams(window.location.search);
      const genreId = urlParams.get("id");
  
      if (!genreId) {
        window.location.href = "/pages/genres.php";
        return;
      }
  
      movieStatus.textContent = "Đang tải danh sách phim...";
      movieStatus.className = "genre-movie-status is-loading";
  
      try {
        const genreResponse = await fetch(`${API_BASE}/genres.php`);
        const genrePayload = await genreResponse.json().catch(() => null);
        
        if (genrePayload && genrePayload.success && Array.isArray(genrePayload.data)) {
          const matchedGenre = genrePayload.data.find(g => g.id == genreId);
          if (matchedGenre) {
            movieTitle.textContent = `Phim ${matchedGenre.name}`;
            document.title = `Phim ${matchedGenre.name} - ThauPhim`;
          }
        }
  
        const moviesResponse = await fetch(`${API_BASE}/movies.php?genre_id=${genreId}&limit=20`);
        const moviesPayload = await moviesResponse.json().catch(() => null);
  
        movieList.replaceChildren();
  
        if (!moviesResponse.ok || !moviesPayload?.success) {
          throw new Error(moviesPayload?.message || "Lỗi tải phim");
        }
  
        const movies = Array.isArray(moviesPayload.data) ? moviesPayload.data : [];
        if (movies.length === 0) {
          movieStatus.className = "genre-movie-status is-empty";
          movieStatus.innerHTML = '<i class="fa-solid fa-clapperboard" aria-hidden="true"></i><strong>Chưa có phim phù hợp</strong><span>Thể loại này sẽ sớm được cập nhật phim mới.</span>';
          return;
        }
  
        const fragment = document.createDocumentFragment();
        movies.forEach((movie) => fragment.append(createMovieCard(movie)));
        movieList.append(fragment);
  
        movieStatus.textContent = `Đã tìm thấy ${movies.length} bộ phim phù hợp.`;
        movieStatus.className = "genre-movie-status is-ready";
  
      } catch (error) {
        console.error("Lỗi:", error);
        movieList.replaceChildren();
        movieStatus.className = "genre-movie-status is-error";
        movieStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>Không thể tải danh sách phim</strong><span>Vui lòng kiểm tra kết nối và thử lại.</span>';
      }
    };
  
    loadGenreMovies();
  });