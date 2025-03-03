const visitorId=123;

//subscribe to agent's private channel
window.Echo.private(`visitor.${visitorId}`)
.listen('NewMessage',(data)=>{
    console.log('New Message:',data.message);
});