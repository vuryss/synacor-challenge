import time
import sys, resource

resource.setrlimit(resource.RLIMIT_STACK, (resource.RLIM_INFINITY, resource.RLIM_INFINITY))

sys.setrecursionlimit(100000)

start = time.time()

v0 = 4
v1 = 1
v7 = 1


def a(r0, r1, r7):
    if r0 == 0:
        return (r1 + 1) % 32768

    if r1 == 0:
        return a(r0 - 1, r7, r7)

    return a(r0 - 1, a(r0, r1 - 1, r7), r7)


print('Starting with ' + str(v0) + ' ' + str(v1) + ' ' + str(v7))

result = a(v0, v1, v7)

print('Completed in ' + str(time.time() - start) + ' seconds')
print('R0: ' + str(result))
print('R1: ' + str(result - 1))
print('R7: ' + str(v7))
