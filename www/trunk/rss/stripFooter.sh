#!/bin/bash

touch temp;
sed '$d' < $1 > temp;
sed '$d' < temp > $1;
 