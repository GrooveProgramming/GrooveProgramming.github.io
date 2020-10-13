let user = window.location.hash;
user = user.replace('#', "/");

fetch("https://scratchdb.lefty.one/v2/user/info" + user).then(res => res.json()).then((data) => {
  document.getElementById("username: ").innerHTML ="username: " + data.username;
  document.getElementById("bio: ").innerHTML ="bio: " + data.bio; 
  document.getElementById("work: ").innerHTML ="work: " + data.work; 
  document.getElementById("following: ").innerHTML ="following: " + data.following;
  document.getElementById("followers: ").innerHTML ="followers: " + data.followers;
  fetch("https://scratch.mit.edu/site-api/comments/user" + user + '/').then(res => res.text()).then((data) => {
      document.getElementById("comments: ").innerHTML ="comments: " + data 
  }).catch((e)=>{
    console.log("https://scratch.mit.edu/site-api/comments/user" + user + '/');
    fetch("https://scratchdb.lefty.one/v2/user/info/mres").then(res => res.json()).then((data) => {
      document.getElementById("username: ").innerHTML ="username: " + data.username;
      document.getElementById("bio: ").innerHTML ="bio: " + data.bio; 
    });
