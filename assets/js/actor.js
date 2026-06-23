console.log("actor.js đã chạy");
const actorList = document.querySelector("#actorList");
const actorUrl = `https://api.themoviedb.org/3/person/popular?api_key=${TMDB_API_KEY}&language=vi-VN&page=1`;
const loadActors = async () => {
  if (!actorList) return;

  try {
    const response = await fetch(actorUrl);
    const data = await response.json();

    actorList.innerHTML = data.results
      .slice(0, 20)
      .map(
        (actor) => `
          <div class="actor-card">
            <img
              src="https://image.tmdb.org/t/p/w500${actor.profile_path}"
              alt="${actor.name}"
            >
            <h3>${actor.name}</h3>
          </div>
        `,
      )
      .join("");
  } catch (error) {
    console.error(error);
  }
};

loadActors();
