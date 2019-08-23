## Turtle path crossing

You are provided with an array with index 0, which contains of N positive numbers.

Turtle stands at the position (0,0) and moves to north.  It moves A[0] steps in front and then turns in 90 degrees clock wise. After that it moves A[1] steps, and does another 90 degrees turn, ...

Example:

    A = [1, 3, 2, 5, 4, 4, 6, 3, 2]
    
Turtle moves like this:

    (0,0)   -> (0,1)     first move, 1 step to north
	(0,1)   -> (3,1)     second move, 3 steps to east 
	(3,1)   -> (3,-1)    third move, 2 steps to south
	(3,-1)  -> (-2,-1)   fourth move, 5 steps to west
	(-2,-1) -> (-2,3)    fifth move, 4 steps to north
	(-2,3)  -> (2,3)     sixth move, 4 steps to east
	(2,3)   -> (2,-3)    seventh move, 6 steps to the south
	(2,-3)  -> (-1 -3)   eight move, 3 steps to west
	(-1,-3) -> (-1,-1)   nineth move, 2 steps to north
	
On 7th and 9th step turtle made previous path crossing.

Write function that returns first move in which turtle makes previous path crossing. If it doesn't cross, you should return 0.

In previous example, the return should be 7

Assume:
- Possible number of elements in array from [1 ... 100,000]
- Every element of an array is an integer in span [1..1,000,000]

Information of expected complexity of algorithm:
- time complexity, in worst case O(n)
- space complexity, in worst case O(1), not including input data