fetch("https://api.scratch.mit.edu/proxy/featured").then(res => res.json()).then((data) => {
    document.getElementById("featured1").innerHTML ="" + data.community_featured_projects[0].title;
  });
