# DbRecord

## Introduction

DbRecord es una librería que nos permite operar con bases de datos sin necesidad de ejecutar manualmente sentencias SQL. A diferencia de otras librerías similares, DbRecord nos permite **operar sobre varias tablas al mismo tiempo**, lo cual redunda en un código más conciso y claro.

Supongamos que tenemos una tabla principal (table0) de la que penden tres tablas secundarias (table1, table2 y table3) a través de los campos table1_id, table2_id y table3_id. Esto es:  
![test](https://cloud.githubusercontent.com/assets/5312427/12149778/ec2fa156-b4a5-11e5-8697-f423856bb3cd.png)
