function to_proper_case(str)
{
    var noCaps = ['of','a','the','and','an','am','or','nor','but','is','if','then', 
'else','when','at','from','by','on','off','for','in','out','to','into','with'];
    return str.replace(/\w\S*/g, function(txt, offset){
        if(offset != 0 && noCaps.indexOf(txt.toLowerCase()) != -1){
            return txt.toLowerCase();    
        }
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}