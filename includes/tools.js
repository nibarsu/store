//請勿刪除

function viewobj(obj){
  if (typeof(obj)=="object"){
       for ( var propName in obj ) {
             alert( propName + " : " + obj[propName] );
       }
  }
}

//除法
function division(num1,num2){
     var t1=0,t2=0,r1,r2;
     try{t1=num1.toString().split(".")[1].length}catch(e){}
     try{t2=num2.toString().split(".")[1].length}catch(e){}
     with(Math){
     r1=Number(num1.toString().replace(".",""))
     r2=Number(num2.toString().replace(".",""))
     return (r1/r2)*pow(10,t2-t1);
     }
}

//乘法
function multiplication(num1,num2)
{
     var m=0,s1=num1.toString(),s2=num2.toString();
     try{m+=s1.split(".")[1].length}catch(e){}
     try{m+=s2.split(".")[1].length}catch(e){}
     return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)
}

//加法
function addition(num1,num2){
     var r1,r2,m;
     try{r1=num1.toString().split(".")[1].length}catch(e){r1=0}
     try{r2=num2.toString().split(".")[1].length}catch(e){r2=0}
     m=Math.pow(10,Math.max(r1,r2));
     return (num1*m+num2*m)/m;
}
